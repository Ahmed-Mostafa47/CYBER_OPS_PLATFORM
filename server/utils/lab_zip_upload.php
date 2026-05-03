<?php
declare(strict_types=1);

require_once __DIR__ . '/pdo_mysqli_shim.php';

function hackme_lab_storage_root(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'labs';
}

function hackme_lab_storage_tmp_root(): string
{
    return hackme_lab_storage_root() . DIRECTORY_SEPARATOR . '_tmp';
}

function hackme_ensure_dir(string $path): bool
{
    if (is_dir($path)) {
        return true;
    }
    return @mkdir($path, 0775, true);
}

function hackme_normalize_zip_path(string $name): string
{
    $name = str_replace('\\', '/', $name);
    $name = preg_replace('/\/+/', '/', $name) ?? '';
    return trim($name, '/');
}

function hackme_is_safe_zip_entry(string $path): bool
{
    if ($path === '') {
        return false;
    }
    if (strpos($path, "\0") !== false) {
        return false;
    }
    if (preg_match('/^[A-Za-z]:\//', $path) === 1) {
        return false;
    }
    if (str_starts_with($path, '/')) {
        return false;
    }
    $parts = explode('/', $path);
    foreach ($parts as $part) {
        if ($part === '..') {
            return false;
        }
    }
    return true;
}

/**
 * True if the entry path is inside a lab-files/ directory (at ZIP root or under one top-level folder).
 * Folder name match is case-insensitive (Windows ZIP tools often use Lab-Files).
 */
function hackme_zip_entry_in_lab_files_tree(string $entryName): bool
{
    if ($entryName === '') {
        return false;
    }
    $lower = strtolower($entryName);
    if (str_starts_with($lower, 'lab-files/')) {
        return true;
    }

    return preg_match('#(^|/)lab-files/.#', $lower) === 1;
}

/**
 * True if entry is metadata.json at root or in a nested folder (e.g. MyLab/metadata.json).
 */
function hackme_zip_entry_is_metadata_json(string $entryName): bool
{
    return strcasecmp($entryName, 'metadata.json') === 0
        || preg_match('#(^|/)metadata\.json$#i', $entryName) === 1;
}

/**
 * Light scan of the ZIP head (no full-file load) for a soft warning only.
 */
function hackme_zip_quick_scan_warning(string $path): string
{
    $h = @fopen($path, 'rb');
    if ($h === false) {
        return '';
    }
    $maxRead = 2 * 1024 * 1024;
    $read = 0;
    while (!feof($h) && $read < $maxRead) {
        $chunk = fread($h, 65536);
        if ($chunk === false || $chunk === '') {
            break;
        }
        $read += strlen($chunk);
        $lower = strtolower($chunk);
        if (str_contains($lower, '<?php') || str_contains($lower, '<script')) {
            fclose($h);
            return 'Archive contains executable-looking content; ensure this lab stays isolated.';
        }
    }
    fclose($h);

    return '';
}

/** True if file looks like a ZIP (local header magic). */
function hackme_is_zip_magic(string $path): bool
{
    $h = @fopen($path, 'rb');
    if ($h === false) {
        return false;
    }
    $sig = fread($h, 4);
    fclose($h);

    return $sig === "PK\x03\x04" || $sig === "PK\x05\x06" || $sig === "PK\x07\x08";
}

/** True if file looks like a classic RAR archive (RAR 4 / 5 header). */
function hackme_is_rar_magic(string $path): bool
{
    $h = @fopen($path, 'rb');
    if ($h === false) {
        return false;
    }
    $sig = fread($h, 8);
    fclose($h);

    return strlen($sig) >= 7 && str_starts_with($sig, "Rar!\x1a\x07");
}

function hackme_lab_archive_supports_rar(): bool
{
    return class_exists('RarArchive');
}

function hackme_slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');
    if ($value === '') {
        $value = 'lab';
    }
    return substr($value, 0, 64);
}

function hackme_validate_metadata(array $meta): array
{
    $errors = [];
    $title = trim((string) ($meta['title'] ?? ''));
    $difficulty = strtolower(trim((string) ($meta['difficulty'] ?? '')));
    $category = trim((string) ($meta['category'] ?? ''));
    $typeRaw = strtolower(trim((string) ($meta['type'] ?? '')));
    // Accept common aliases from hand-written JSON
    $type = match ($typeRaw) {
        'white_box', 'white-box', 'wb' => 'whitebox',
        'black_box', 'black-box', 'bb' => 'blackbox',
        default => $typeRaw,
    };

    if ($title === '') {
        $errors[] = 'metadata.title is required.';
    }
    if (!in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
        $errors[] = 'metadata.difficulty must be easy, medium, or hard (any case).';
    }
    if ($category === '') {
        $errors[] = 'metadata.category is required.';
    }
    if (!in_array($type, ['whitebox', 'blackbox'], true)) {
        $errors[] = 'metadata.type must be whitebox or blackbox (e.g. "type": "whitebox").';
    }

    return [
        'errors' => $errors,
        'metadata' => [
            'title' => $title,
            'difficulty' => $difficulty,
            'category' => $category,
            'type' => $type, // normalized whitebox|blackbox
        ],
    ];
}

function hackme_recursive_remove(string $path): void
{
    if (!file_exists($path)) {
        return;
    }
    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }
    $items = scandir($path);
    if (!is_array($items)) {
        return;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        hackme_recursive_remove($path . DIRECTORY_SEPARATOR . $item);
    }
    @rmdir($path);
}

function hackme_validate_and_stage_lab_zip(string $uploadedTmpPath, int $maxBytes = 20971520, string $fallbackTitle = ''): array
{
    $errors = [];
    $warnings = [];
    $maxEntries = 500;

    if (!is_file($uploadedTmpPath) || !is_readable($uploadedTmpPath)) {
        return ['success' => false, 'errors' => ['Uploaded file is not readable.']];
    }
    $size = filesize($uploadedTmpPath);
    if ($size === false || $size < 1) {
        return ['success' => false, 'errors' => ['Uploaded ZIP is empty.']];
    }
    if ($size > $maxBytes) {
        return ['success' => false, 'errors' => ['ZIP exceeds maximum allowed size (20 MB).']];
    }

    $zip = new ZipArchive();
    if ($zip->open($uploadedTmpPath) !== true) {
        return ['success' => false, 'errors' => ['Failed to open ZIP archive.']];
    }

    if ($zip->numFiles < 1 || $zip->numFiles > $maxEntries) {
        $zip->close();
        return ['success' => false, 'errors' => ['ZIP entry count is invalid (1-500 required).']];
    }

    $hasLabFiles = false;
    $metadataRaw = '';
    $hasMetadata = false;

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        if (!is_array($stat)) {
            $errors[] = 'Invalid ZIP entry metadata.';
            continue;
        }
        $entryName = hackme_normalize_zip_path((string) ($stat['name'] ?? ''));
        if ($entryName === '' || !hackme_is_safe_zip_entry($entryName)) {
            $errors[] = 'Unsafe ZIP path detected.';
            continue;
        }

        if (hackme_zip_entry_in_lab_files_tree($entryName)) {
            if (!str_ends_with($entryName, '/')) {
                $hasLabFiles = true;
            }
            $lower = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
            if ($lower !== '' && !in_array($lower, ['php', 'js', 'html', 'css', 'json', 'txt', 'md', 'sql', 'py'], true)) {
                $warnings[] = 'Potentially risky file extension inside lab-files: ' . $entryName;
            }
        }

        if (!$hasMetadata && hackme_zip_entry_is_metadata_json($entryName)) {
            $hasMetadata = true;
            $metadataRaw = (string) $zip->getFromIndex($i);
        }
    }

    if (!$hasLabFiles) {
        $errors[] = 'No files under lab-files/. Use layout: lab-files/… at ZIP root, or one folder (e.g. MyLab/lab-files/…). Optional metadata.json (title, difficulty, category, type) at root or beside lab-files.';
    }

    $meta = [];
    if ($hasMetadata) {
        $meta = json_decode($metadataRaw, true);
        if (!is_array($meta)) {
            $errors[] = 'metadata.json is invalid JSON.';
            $meta = [];
        }
    } else {
        $meta = [
            'title' => trim($fallbackTitle) !== '' ? trim($fallbackTitle) : 'Uploaded Lab',
            'difficulty' => 'medium',
            'category' => 'General',
            'type' => 'whitebox',
        ];
    }
    $metaCheck = hackme_validate_metadata($meta);
    foreach ($metaCheck['errors'] as $err) {
        $errors[] = $err;
    }

    if ($errors !== []) {
        $zip->close();
        return ['success' => false, 'errors' => array_values(array_unique($errors)), 'warnings' => array_values(array_unique($warnings))];
    }

    $tmpRoot = hackme_lab_storage_tmp_root();
    if (!hackme_ensure_dir($tmpRoot)) {
        $zip->close();
        return ['success' => false, 'errors' => ['Failed to create temporary storage directory.']];
    }
    $token = bin2hex(random_bytes(16));
    $stageDir = $tmpRoot . DIRECTORY_SEPARATOR . $token;
    $extractDir = $stageDir . DIRECTORY_SEPARATOR . 'content';
    if (!hackme_ensure_dir($extractDir)) {
        $zip->close();
        return ['success' => false, 'errors' => ['Failed to create staging directory.']];
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        if (!is_array($stat)) {
            continue;
        }
        $entryName = hackme_normalize_zip_path((string) ($stat['name'] ?? ''));
        if ($entryName === '' || !hackme_is_safe_zip_entry($entryName)) {
            continue;
        }
        $targetPath = $extractDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $entryName);
        if (str_ends_with($entryName, '/')) {
            hackme_ensure_dir($targetPath);
            continue;
        }
        $parent = dirname($targetPath);
        if (!hackme_ensure_dir($parent)) {
            $zip->close();
            hackme_recursive_remove($stageDir);
            return ['success' => false, 'errors' => ['Failed to create destination directory for extraction.']];
        }
        $stream = $zip->getStream((string) ($stat['name'] ?? ''));
        if ($stream === false) {
            $zip->close();
            hackme_recursive_remove($stageDir);
            return ['success' => false, 'errors' => ['Failed to read ZIP entry stream.']];
        }
        $fh = fopen($targetPath, 'wb');
        if ($fh === false) {
            fclose($stream);
            $zip->close();
            hackme_recursive_remove($stageDir);
            return ['success' => false, 'errors' => ['Failed to write extracted file.']];
        }
        stream_copy_to_stream($stream, $fh);
        fclose($fh);
        fclose($stream);
    }
    $zip->close();

    $manifest = [
        'token' => $token,
        'created_at' => time(),
        'metadata' => $metaCheck['metadata'],
        'warnings' => array_values(array_unique($warnings)),
    ];
    $manifestPath = $stageDir . DIRECTORY_SEPARATOR . 'manifest.json';
    $manifestJson = json_encode(
        $manifest,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
    if ($manifestJson === false) {
        hackme_recursive_remove($stageDir);

        return ['success' => false, 'errors' => ['Failed to serialize staging manifest.']];
    }
    if (@file_put_contents($manifestPath, $manifestJson) === false) {
        hackme_recursive_remove($stageDir);

        return ['success' => false, 'errors' => ['Failed to write staging manifest (check server/storage permissions).']];
    }

    return [
        'success' => true,
        'token' => $token,
        'metadata' => $metaCheck['metadata'],
        'warnings' => $manifest['warnings'],
        'validation' => [
            'required_files' => ['lab-files/'],
            'lab_files_detected' => true,
            'metadata_provided' => $hasMetadata,
            'safe_extraction' => true,
            'archive_format' => 'zip',
        ],
    ];
}

/**
 * Same staging contract as hackme_validate_and_stage_lab_zip (manifest + content/), for RAR.
 * Requires PHP rar extension (RarArchive).
 */
function hackme_validate_and_stage_lab_rar(string $uploadedTmpPath, int $maxBytes = 20971520, string $fallbackTitle = ''): array
{
    $errors = [];
    $warnings = [];
    $maxEntries = 500;

    if (!hackme_lab_archive_supports_rar()) {
        return ['success' => false, 'errors' => ['PHP rar extension is not loaded. Install it (pecl install rar) or use a .zip package instead.']];
    }

    if (!is_file($uploadedTmpPath) || !is_readable($uploadedTmpPath)) {
        return ['success' => false, 'errors' => ['Uploaded file is not readable.']];
    }
    $size = filesize($uploadedTmpPath);
    if ($size === false || $size < 1) {
        return ['success' => false, 'errors' => ['Uploaded archive is empty.']];
    }
    if ($size > $maxBytes) {
        return ['success' => false, 'errors' => ['Archive exceeds maximum allowed size (20 MB).']];
    }

    $rar = RarArchive::open($uploadedTmpPath);
    if ($rar === false) {
        return ['success' => false, 'errors' => ['Failed to open RAR archive (corrupt file, unsupported variant, or password-protected archive).']];
    }

    $entries = $rar->getEntries();
    if (!is_array($entries) || $entries === []) {
        $rar->close();

        return ['success' => false, 'errors' => ['RAR archive has no entries.']];
    }

    $entryCount = 0;
    foreach ($entries as $e) {
        if ($e instanceof RarEntry) {
            $entryCount++;
        }
    }
    if ($entryCount < 1 || $entryCount > $maxEntries) {
        $rar->close();

        return ['success' => false, 'errors' => ['RAR entry count is invalid (1-500 required).']];
    }

    $hasLabFiles = false;
    $metadataRaw = '';
    $hasMetadata = false;

    foreach ($entries as $entry) {
        if (!($entry instanceof RarEntry)) {
            continue;
        }
        $entryName = hackme_normalize_zip_path($entry->getName());
        if ($entryName === '' || !hackme_is_safe_zip_entry($entryName)) {
            $errors[] = 'Unsafe RAR path detected.';
            continue;
        }

        if (hackme_zip_entry_in_lab_files_tree($entryName)) {
            if (!$entry->isDirectory()) {
                $hasLabFiles = true;
            }
            $lower = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
            if ($lower !== '' && !in_array($lower, ['php', 'js', 'html', 'css', 'json', 'txt', 'md', 'sql', 'py'], true)) {
                $warnings[] = 'Potentially risky file extension inside lab-files: ' . $entryName;
            }
        }

        if (!$hasMetadata && hackme_zip_entry_is_metadata_json($entryName) && !$entry->isDirectory()) {
            $stream = $entry->getStream();
            if ($stream !== false) {
                $metadataRaw = (string) stream_get_contents($stream);
                fclose($stream);
                $hasMetadata = true;
            }
        }
    }

    if (!$hasLabFiles) {
        $errors[] = 'No files under lab-files/. Use layout: lab-files/… at archive root, or one folder (e.g. MyLab/lab-files/…). Optional metadata.json (title, difficulty, category, type) at root or beside lab-files.';
    }

    $meta = [];
    if ($hasMetadata && $metadataRaw !== '') {
        $meta = json_decode($metadataRaw, true);
        if (!is_array($meta)) {
            $errors[] = 'metadata.json is invalid JSON.';
            $meta = [];
        }
    } else {
        $meta = [
            'title' => trim($fallbackTitle) !== '' ? trim($fallbackTitle) : 'Uploaded Lab',
            'difficulty' => 'medium',
            'category' => 'General',
            'type' => 'whitebox',
        ];
    }
    $metaCheck = hackme_validate_metadata($meta);
    foreach ($metaCheck['errors'] as $err) {
        $errors[] = $err;
    }

    if ($errors !== []) {
        $rar->close();

        return ['success' => false, 'errors' => array_values(array_unique($errors)), 'warnings' => array_values(array_unique($warnings))];
    }

    $tmpRoot = hackme_lab_storage_tmp_root();
    if (!hackme_ensure_dir($tmpRoot)) {
        $rar->close();

        return ['success' => false, 'errors' => ['Failed to create temporary storage directory.']];
    }
    $token = bin2hex(random_bytes(16));
    $stageDir = $tmpRoot . DIRECTORY_SEPARATOR . $token;
    $extractDir = $stageDir . DIRECTORY_SEPARATOR . 'content';
    if (!hackme_ensure_dir($extractDir)) {
        $rar->close();

        return ['success' => false, 'errors' => ['Failed to create staging directory.']];
    }

    foreach ($entries as $entry) {
        if (!($entry instanceof RarEntry)) {
            continue;
        }
        $entryName = hackme_normalize_zip_path($entry->getName());
        if ($entryName === '' || !hackme_is_safe_zip_entry($entryName)) {
            continue;
        }
        $targetPath = $extractDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $entryName);
        if ($entry->isDirectory()) {
            hackme_ensure_dir($targetPath);
            continue;
        }
        $parent = dirname($targetPath);
        if (!hackme_ensure_dir($parent)) {
            $rar->close();
            hackme_recursive_remove($stageDir);

            return ['success' => false, 'errors' => ['Failed to create destination directory for extraction.']];
        }
        $stream = $entry->getStream();
        if ($stream === false) {
            $rar->close();
            hackme_recursive_remove($stageDir);

            return ['success' => false, 'errors' => ['Failed to read RAR entry stream (encrypted or solid archive?).']];
        }
        $fh = fopen($targetPath, 'wb');
        if ($fh === false) {
            fclose($stream);
            $rar->close();
            hackme_recursive_remove($stageDir);

            return ['success' => false, 'errors' => ['Failed to write extracted file.']];
        }
        stream_copy_to_stream($stream, $fh);
        fclose($fh);
        fclose($stream);
    }

    $rar->close();

    $manifest = [
        'token' => $token,
        'created_at' => time(),
        'metadata' => $metaCheck['metadata'],
        'warnings' => array_values(array_unique($warnings)),
    ];
    $manifestPath = $stageDir . DIRECTORY_SEPARATOR . 'manifest.json';
    $manifestJson = json_encode(
        $manifest,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
    if ($manifestJson === false) {
        hackme_recursive_remove($stageDir);

        return ['success' => false, 'errors' => ['Failed to serialize staging manifest.']];
    }
    if (@file_put_contents($manifestPath, $manifestJson) === false) {
        hackme_recursive_remove($stageDir);

        return ['success' => false, 'errors' => ['Failed to write staging manifest (check server/storage permissions).']];
    }

    return [
        'success' => true,
        'token' => $token,
        'metadata' => $metaCheck['metadata'],
        'warnings' => $manifest['warnings'],
        'validation' => [
            'required_files' => ['lab-files/'],
            'lab_files_detected' => true,
            'metadata_provided' => $hasMetadata,
            'safe_extraction' => true,
            'archive_format' => 'rar',
        ],
    ];
}

function hackme_load_staged_manifest(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
        return null;
    }
    $stageDir = hackme_lab_storage_tmp_root() . DIRECTORY_SEPARATOR . $token;
    $manifestPath = $stageDir . DIRECTORY_SEPARATOR . 'manifest.json';
    if (!is_file($manifestPath)) {
        return null;
    }
    $raw = file_get_contents($manifestPath);
    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        return null;
    }
    $data['stage_dir'] = $stageDir;
    $data['extract_dir'] = $stageDir . DIRECTORY_SEPARATOR . 'content';
    return $data;
}

function hackme_user_is_instructor_or_admin(PdoMysqliShim $conn, int $userId): bool
{
    $uid = (int) $userId;
    if ($uid < 1) {
        return false;
    }
    $sql = "
      SELECT 1
      FROM user_roles ur
      INNER JOIN roles r ON r.role_id = ur.role_id
      WHERE ur.user_id = $uid
        AND LOWER(r.name) IN ('admin','superadmin','instructor')
      LIMIT 1
    ";
    $res = $conn->query($sql);
    return $res && $res->num_rows > 0;
}
