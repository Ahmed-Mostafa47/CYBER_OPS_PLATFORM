<?php
require_once __DIR__ . '/server/helpers/whitebox/whitebox_sqli_verify.php';

$testFile = __DIR__ . '/scratch/test_lint.php';
file_put_contents($testFile, "<?php echo 'hello'; ?>");

$res = whitebox_php_lint($testFile);
echo "Lint result: " . json_encode($res) . "\n";

$php = whitebox_find_php_cli();
echo "PHP CLI found: $php\n";

$cmd = escapeshellarg($php) . ' -v';
exec($cmd, $out, $code);
echo "PHP -v status: $code\n";
print_r($out);

@unlink($testFile);
