<?php
declare(strict_types=1);

/**
 * Insert a catalog lab row from proposal-style fields (same rules as approve_lab_submission).
 *
 * @return array{lab_id: int, owasp_category_key: string}
 */
function hackme_insert_lab_catalog_card_from_proposal_fields(
    PdoMysqliShim $conn,
    bool $proposalColsReady,
    int $createdByUserId,
    string $title,
    string $description,
    int $labtypeId,
    string $difficulty,
    int $pointsTotal,
    string $owaspRaw
): array {
    if ($labtypeId < 1 || $labtypeId > 3) {
        $labtypeId = 1;
    }
    $diffRaw = strtolower(trim($difficulty));
    $difficultyNorm = in_array($diffRaw, ['easy', 'medium', 'hard'], true) ? $diffRaw : 'easy';
    $pointsNorm = max(0, $pointsTotal);
    $owaspKeyRaw = preg_replace('/[^a-zA-Z0-9_]/', '_', trim($owaspRaw));
    $owaspKey = is_string($owaspKeyRaw) ? substr($owaspKeyRaw, 0, 128) : '';
    if ($owaspKey === '') {
        $owaspKey = 'a01_broken_access_control';
    }

    $titleEsc = $conn->real_escape_string(substr(trim($title), 0, 255));
    $iconEsc = $conn->real_escape_string('🧪');
    $owaspEsc = $conn->real_escape_string($owaspKey);
    $marker = '[[hackme_owasp:' . $owaspKey . "]]\n\n";
    $descPlainEsc = $conn->real_escape_string(trim($description));
    $descWithMarkerEsc = $conn->real_escape_string($marker . trim($description));

    if ($proposalColsReady) {
        $sqlIns = "
            INSERT INTO labs (
                title, description, labtype_id, difficulty, points_total,
                created_by, is_published, visibility, docker_image, reset_interval,
                icon, port, launch_path, owasp_category_key, coming_soon
            ) VALUES (
                '$titleEsc', '$descPlainEsc', $labtypeId, '$difficultyNorm', $pointsNorm,
                $createdByUserId, 1, 'public', '', 3600,
                '$iconEsc', NULL, '', '$owaspEsc', 1
            )
        ";
    } else {
        $sqlIns = "
            INSERT INTO labs (
                title, description, labtype_id, difficulty, points_total,
                created_by, is_published, visibility, docker_image, reset_interval,
                icon, port, launch_path
            ) VALUES (
                '$titleEsc', '$descWithMarkerEsc', $labtypeId, '$difficultyNorm', $pointsNorm,
                $createdByUserId, 1, 'public', '', 3600,
                '$iconEsc', NULL, '__HACKME_SOON__'
            )
        ";
    }
    $ins = $conn->query($sqlIns);
    if ($ins !== true) {
        throw new RuntimeException('Failed to create lab row: ' . ($conn->error ?: 'DB error'));
    }
    $newLabId = (int) $conn->insert_id;
    if ($newLabId < 1) {
        throw new RuntimeException('Failed to allocate lab id');
    }

    return [
        'lab_id' => $newLabId,
        'owasp_category_key' => $owaspKey,
    ];
}
