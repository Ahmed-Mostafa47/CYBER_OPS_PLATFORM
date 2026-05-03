<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    require_once __DIR__ . '/../../utils/db_connect.php';
    require_once __DIR__ . '/../../utils/lab_zip_upload.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Load error']);
    exit;
}

if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
$conn->set_charset('utf8mb4');

try {
    $userId = (int) ($_POST['user_id'] ?? 0);
    if (!hackme_user_is_instructor_or_admin($conn, $userId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit;
    }

    if (!isset($_FILES['lab_zip']) || !is_array($_FILES['lab_zip'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'lab_zip file is required']);
        exit;
    }

    $file = $_FILES['lab_zip'];
    $name = (string) ($file['name'] ?? '');
    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($err !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Upload failed', 'data' => ['upload_error' => $err]]);
        exit;
    }

    $isZip = preg_match('/\.zip$/i', $name) === 1;
    $isRar = preg_match('/\.rar$/i', $name) === 1;
    if (!$isZip && !$isRar) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Only .zip or .rar files are allowed']);
        exit;
    }

    if ($isZip && !class_exists('ZipArchive')) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Server is missing the PHP zip extension (ZipArchive). Enable extension=zip in php.ini and restart Apache.',
        ]);
        exit;
    }

    if ($isRar && !hackme_lab_archive_supports_rar()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'RAR uploads need the PHP rar extension. Install it (pecl install rar) or export your lab as a .zip file.',
            'data' => [
                'hint' => 'https://www.php.net/manual/en/rar.installation.php',
            ],
        ]);
        exit;
    }

    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
    $mimeRaw = $finfo ? (string) finfo_file($finfo, $tmpPath) : '';
    if ($finfo) {
        finfo_close($finfo);
    }
    $mimeRaw = trim($mimeRaw);
    $mimeBase = strtolower(trim(explode(';', $mimeRaw, 2)[0]));

    $mimeOk = false;
    if ($isZip) {
        $allowedZipMimes = [
            'application/zip',
            'application/x-zip-compressed',
            'application/x-zip',
            'application/zip-compressed',
            'application/octet-stream',
            'binary/octet-stream',
        ];
        $zipProbe = new ZipArchive();
        $opensAsZip = $zipProbe->open($tmpPath) === true;
        if ($opensAsZip) {
            $zipProbe->close();
        }
        $mimeOk = $opensAsZip
            || $mimeBase === ''
            || in_array($mimeBase, $allowedZipMimes, true)
            || hackme_is_zip_magic($tmpPath);
        if (!$mimeOk) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid MIME type for ZIP',
                'data' => [
                    'detected_mime' => $mimeRaw !== '' ? $mimeRaw : '(empty — finfo unavailable or unknown)',
                    'hint' => 'If this is a real .zip file, try re-saving it or use 7-Zip. The server accepts the file when it opens as ZIP regardless of MIME.',
                ],
            ]);
            exit;
        }
    } else {
        $allowedRarMimes = [
            'application/x-rar-compressed',
            'application/vnd.rar',
            'application/rar',
            'application/octet-stream',
            'binary/octet-stream',
        ];
        $opensAsRar = false;
        if (hackme_lab_archive_supports_rar()) {
            $rarProbe = RarArchive::open($tmpPath);
            $opensAsRar = $rarProbe !== false;
            if ($opensAsRar) {
                $rarProbe->close();
            }
        }
        $mimeOk = $opensAsRar
            || $mimeBase === ''
            || in_array($mimeBase, $allowedRarMimes, true)
            || hackme_is_rar_magic($tmpPath);
        if (!$mimeOk) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid MIME type for RAR',
                'data' => [
                    'detected_mime' => $mimeRaw !== '' ? $mimeRaw : '(empty — finfo unavailable or unknown)',
                    'hint' => 'The server accepts RAR when it opens with the PHP rar extension or matches the RAR file signature.',
                ],
            ]);
            exit;
        }
    }

    $scanWarning = hackme_zip_quick_scan_warning($tmpPath);

    $fallbackTitle = preg_replace('/\.(zip|rar)$/i', '', $name) ?: 'Uploaded Lab';
    $result = $isZip
        ? hackme_validate_and_stage_lab_zip($tmpPath, 20 * 1024 * 1024, $fallbackTitle)
        : hackme_validate_and_stage_lab_rar($tmpPath, 20 * 1024 * 1024, $fallbackTitle);
    if (!$result['success']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $isZip ? 'ZIP validation failed' : 'RAR validation failed',
            'data' => [
                'errors' => $result['errors'] ?? [],
                'warnings' => $result['warnings'] ?? [],
            ],
        ], JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    $warnings = $result['warnings'] ?? [];
    if ($scanWarning !== '') {
        $warnings[] = $scanWarning;
    }

    $payload = [
        'success' => true,
        'message' => $isZip ? 'ZIP validated and staged' : 'RAR validated and staged',
        'data' => [
            'upload_token' => $result['token'],
            'metadata' => $result['metadata'],
            'validation' => $result['validation'] ?? [],
            'warnings' => array_values(array_unique($warnings)),
            'archive_format' => $isZip ? 'zip' : 'rar',
        ],
    ];
    $out = json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE);
    if ($out === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to encode response JSON']);
        exit;
    }
    echo $out;
} catch (Throwable $e) {
    error_log('upload_lab_zip.php: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Archive upload failed on server. Enable PHP zip (and rar for .rar), and ensure HackMe/server/storage/labs is writable.',
        'data' => ['detail' => $e->getMessage()],
    ], JSON_INVALID_UTF8_SUBSTITUTE);
}
