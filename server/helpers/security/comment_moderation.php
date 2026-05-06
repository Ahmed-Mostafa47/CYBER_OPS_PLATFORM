<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/db/pdo_mysqli_shim.php';
require_once __DIR__ . '/../../core/utils/load_env.php';
require_once __DIR__ . '/../../helpers/security/comment_moderation_local.php';

/**
 * Google Gemini API (primary) + Google Perspective API (fallback)
 * + always-on local wordlist (works without API keys).
 */

function hackme_users_has_comment_moderation_columns(PdoMysqliShim $conn): bool
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $r = $conn->query("SHOW COLUMNS FROM users LIKE 'comment_moderation_strikes'");
    $cached = ($r && $r->num_rows > 0);
    return $cached;
}

/**
 * @return array{flagged:bool, provider:string, detail?:string}
 */
function hackme_moderate_comment_text(string $text): array
{
    $localFlagged = hackme_local_profanity_flagged($text);
    if ($localFlagged) {
        return [
            'flagged' => true,
            'provider' => 'local',
            'detail' => 'local_wordlist',
        ];
    }

    $cfg = require __DIR__ . '/../../core/config/moderation.php';
    
    $persp = trim((string) ($cfg['perspective_api_key'] ?? ''));
    if ($persp !== '') {
        $threshold = (float) ($cfg['perspective_threshold'] ?? 0.72);
        $r = hackme_perspective_moderate($text, $persp, $threshold);
        if ($r['ok']) {
            return [
                'flagged' => $r['flagged'],
                'provider' => 'perspective',
                'detail' => $r['detail'] ?? '',
            ];
        }
        error_log('Perspective moderation failed: ' . ($r['error'] ?? 'unknown'));
    }

    $gemini = trim((string) ($cfg['gemini_api_key'] ?? ''));
    if ($gemini !== '') {
        $r = hackme_gemini_moderate($text, $gemini);
        if ($r['ok']) {
            return [
                'flagged' => $r['flagged'],
                'provider' => 'gemini',
                'detail' => $r['detail'] ?? '',
            ];
        }
        error_log('Gemini moderation failed: ' . ($r['error'] ?? 'unknown'));
    }

    return ['flagged' => false, 'provider' => 'none', 'detail' => 'clean'];
}

/**
 * @return array{ok:bool, flagged:bool, detail?:string, error?:string}
 */

/**
 * @return array{ok:bool, flagged:bool, detail?:string, error?:string}
 */
function hackme_gemini_moderate(string $text, string $apiKey): array
{
    // Use gemini-flash-lite-latest - it might have higher limits
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key=' . rawurlencode($apiKey);
    
    $prompt = "You are a strict content moderator for an educational Cybersecurity platform. 
    Analyze the following comment and determine if it violates community standards.
    
    VIOLATIONS INCLUDE:
    - Profanity or vulgar language (STRICTLY focus on Egyptian Arabic slang/insults, even the hidden ones).
    - Hate speech, racism, or religious insults.
    - Harassment, cyber-bullying, or making fun of others' intelligence/skills.
    - Telling someone they are a 'failure', 'stupid', or that they 'don't belong' here.
    - Any negative comment meant to discourage or demean a student.
    - Explicit sexual content or suggestive language.
    - Extreme toxicity or aggressive behavior.
    
    CRITICAL: Be very strict. Even mild insults or slang used to offend others should be flagged.
    
    RESPONSE FORMAT:
    - If it violates any rule: Respond ONLY with 'flagged: [reason in English]'
    - If it is 100% safe: Respond ONLY with 'clean'
    
    Comment to analyze: \"$text\"";

    $payload = json_encode([
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ],
        'generationConfig' => [
            'temperature' => 0.0,
            'maxOutputTokens' => 60
        ],
        'safetySettings' => [
            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_LOW_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_LOW_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_LOW_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_LOW_AND_ABOVE']
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    $maxRetries = 2;
    $attempt = 0;
    $body = false;
    $code = 0;

    while ($attempt <= $maxRetries) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 429) {
            break;
        }

        $attempt++;
        if ($attempt <= $maxRetries) {
            error_log("Gemini hit 429, retrying in 2 seconds... (Attempt $attempt)");
            sleep(2);
        }
    }

    if ($body === false || $code < 200 || $code >= 300) {
        return ['ok' => false, 'flagged' => false, 'error' => 'http_' . $code];
    }

    $json = json_decode((string) $body, true);
    if (!is_array($json)) {
        return ['ok' => false, 'flagged' => false, 'error' => 'invalid_json'];
    }

    $responseContent = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $responseContent = trim(strtolower($responseContent));

    if (strpos($responseContent, 'flagged') !== false) {
        $detail = str_replace('flagged:', '', $responseContent);
        return ['ok' => true, 'flagged' => true, 'detail' => trim($detail)];
    }

    return ['ok' => true, 'flagged' => false, 'detail' => 'clean'];
}

/**
 * @return array{ok:bool, flagged:bool, detail?:string, error?:string}
 */
function hackme_perspective_moderate(string $text, string $apiKey, float $threshold): array
{
    $url = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=' . rawurlencode($apiKey);
    $bodyArr = [
        'comment' => ['text' => $text],
        'requestedAttributes' => [
            'TOXICITY' => new stdClass(),
            'SEVERE_TOXICITY' => new stdClass(),
            'THREAT' => new stdClass(),
            'INSULT' => new stdClass(),
            'PROFANITY' => new stdClass(),
        ],
        'languages' => ['ar', 'en', 'fr'],
        'doNotStore' => true,
    ];
    $payload = json_encode($bodyArr, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $code < 200 || $code >= 300) {
        return ['ok' => false, 'flagged' => false, 'error' => 'http_' . $code];
    }

    $json = json_decode((string) $body, true);
    if (!is_array($json)) {
        return ['ok' => false, 'flagged' => false, 'error' => 'invalid_json'];
    }

    $attrs = $json['attributeScores'] ?? [];
    $max = 0.0;
    $worst = '';
    foreach (['SEVERE_TOXICITY', 'THREAT', 'INSULT', 'PROFANITY', 'TOXICITY'] as $name) {
        if (!isset($attrs[$name]['summaryScore']['value'])) {
            continue;
        }
        $v = (float) $attrs[$name]['summaryScore']['value'];
        if ($v > $max) {
            $max = $v;
            $worst = $name;
        }
    }

    $flagged = $max >= $threshold;

    return [
        'ok' => true,
        'flagged' => $flagged,
        'detail' => $worst !== '' ? $worst . ':' . round($max, 3) : (string) round($max, 3),
    ];
}

/**
 * @return array{banned:bool, until:?string}
 */
function hackme_user_comments_ban_status(PdoMysqliShim $conn, int $userId): array
{
    if (!hackme_users_has_comment_moderation_columns($conn)) {
        return ['banned' => false, 'until' => null];
    }
    $uid = (int) $userId;
    $stmt = $conn->prepare('SELECT comments_banned_until FROM users WHERE user_id = ? LIMIT 1');
    if (!$stmt) {
        return ['banned' => false, 'until' => null];
    }
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows === 0) {
        $stmt->close();
        return ['banned' => false, 'until' => null];
    }
    $row = $res->fetch_assoc();
    $stmt->close();
    $until = $row['comments_banned_until'] ?? null;
    if ($until === null || $until === '') {
        return ['banned' => false, 'until' => null];
    }
    $ts = strtotime((string) $until);
    if ($ts === false) {
        return ['banned' => false, 'until' => null];
    }
    if ($ts > time()) {
        return ['banned' => true, 'until' => (string) $until];
    }
    return ['banned' => false, 'until' => null];
}

/**
 * After a flagged comment: increment strikes, notify, possibly ban.
 *
 * @return array{strikes:int, banned:bool}
 */
function hackme_record_comment_moderation_strike(PdoMysqliShim $conn, int $userId): array
{
    if (!hackme_users_has_comment_moderation_columns($conn)) {
        return ['strikes' => 0, 'banned' => false];
    }

    $uid = (int) $userId;
    $upd = $conn->prepare('UPDATE users SET comment_moderation_strikes = comment_moderation_strikes + 1 WHERE user_id = ?');
    if ($upd) {
        $upd->bind_param('i', $uid);
        $upd->execute();
        $upd->close();
    }

    $strikes = 1;
    $sel = $conn->prepare('SELECT comment_moderation_strikes FROM users WHERE user_id = ? LIMIT 1');
    if ($sel) {
        $sel->bind_param('i', $uid);
        $sel->execute();
        $res = $sel->get_result();
        if ($res && $res->num_rows > 0) {
            $strikes = (int) ($res->fetch_assoc()['comment_moderation_strikes'] ?? 1);
        }
        $sel->close();
    }

    $banned = false;
    if ($strikes >= 2) {
        $banned = true;
        $far = date('Y-m-d H:i:s', strtotime('+50 years'));
        $ban = $conn->prepare('UPDATE users SET comments_banned_until = ? WHERE user_id = ?');
        if ($ban) {
            $ban->bind_param('si', $far, $uid);
            $ban->execute();
            $ban->close();
        }
    }

    return ['strikes' => $strikes, 'banned' => $banned];
}
