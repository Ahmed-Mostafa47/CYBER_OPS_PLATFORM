<?php
require_once __DIR__ . '/../server/helpers/security/comment_moderation.php';

$text = "انت فاشل ومكانك مش هنا";
echo "Testing specific sentence: \"$text\"\n";

$result = hackme_moderate_comment_text($text);
echo "Result: " . ($result['flagged'] ? "🚩 FLAGGED" : "✅ CLEAN") . "\n";
echo "Provider: " . $result['provider'] . "\n";
if (!empty($result['detail'])) {
    echo "Detail: " . $result['detail'] . "\n";
}
if ($result['provider'] === 'none' && strpos(($result['error'] ?? ''), '429') !== false) {
    echo "⚠️ Still hitting Rate Limit (429). Please wait longer before testing again.\n";
}
