<?php
require_once __DIR__ . '/../server/helpers/security/comment_moderation.php';

echo "--- Comment Moderation Test ---\n";

$cfg = require __DIR__ . '/../server/core/config/moderation.php';
if (empty($cfg['gemini_api_key'])) {
    echo "⚠️  WARNING: GEMINI_API_KEY is not set in .env file.\n";
    echo "The system will fall back to local blocklist moderation.\n";
} else {
    echo "✅ Gemini API key detected.\n";
}

$tests = [
    "This is a very helpful comment, thank you!",
    "I will kill you if you don't stop this",
    "Go away you stupid idiot",
    "انت فاشل ومكانك مش هنا",
];

foreach ($tests as $text) {
    echo "\nTesting: \"$text\"\n";
    $result = hackme_moderate_comment_text($text);
    echo "Result: " . ($result['flagged'] ? "🚩 FLAGGED" : "✅ CLEAN") . "\n";
    echo "Provider: " . $result['provider'] . "\n";
    if (!empty($result['detail'])) {
        echo "Detail: " . $result['detail'] . "\n";
    }
    // Wait longer to avoid rate limits during testing
    sleep(4);
}
