<?php
require_once __DIR__ . '/../server/core/utils/load_env.php';
$apiKey = getenv('GEMINI_API_KEY');

if (!$apiKey) {
    die("No API key found in .env\n");
}

echo "Testing Gemini API connection...\n";

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "Available Models:\n";
    foreach ($data['models'] as $model) {
        echo "- " . $model['name'] . "\n";
    }
} else {
    echo "Error Response: $response\n";
}
