<?php
declare(strict_types=1);

/**
 * White-box Lab #19: IDOR — profile loaded by `user_id` from the URL without binding to the logged-in viewer.
 */
function hackme_whitebox_lab19_profile_stub(): string
{
    return <<<'PHP'
<?php
session_start();
$_SESSION['user_id'] = 1;
$userId = (int)($_GET['user_id'] ?? 0);
if ($userId === 1) {
    echo 'PROFILE_SECRET_ALICE';
} elseif ($userId === 2) {
    echo 'PROFILE_SECRET_BOB';
} else {
    echo 'not found';
}
PHP;
}

function hackme_whitebox_lab19_stub_for_relative_path(string $rel): string
{
    $norm = str_replace('\\', '/', trim($rel, " \t\n\r\0\x0B"));
    $norm = ltrim($norm, '/');

    if ($norm === 'lab2/api/get_profile.php') {
        return hackme_whitebox_lab19_profile_stub();
    }
    if ($norm === 'lab2/api/update_email.php') {
        return <<<'PHP'
<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');
echo "Entry point. Some links use ?user_id= in the query string — compare with server-side session identity.\n";
PHP;
    }
    if ($norm === 'lab2/api/get_users.php') {
        return <<<'PHP'
<?php
declare(strict_types=1);
/**
 * Lab scaffolding: profile responses must be scoped to the authenticated user.
 * Never trust a client-supplied object id for horizontal access without an ownership check.
 */
PHP;
    }

    return "<?php\n// Lab 19 bundle file: {$norm}\n";
}

function hackme_whitebox_lab19_meta(): array
{
    return [
        'version' => 1,
        'verify_profile' => 'lab19_idor_user_param',
        'files' => [
            ['id' => 'config', 'display_name' => 'config.php', 'relative_path' => 'lab2/api/config.php'],
            ['id' => 'get_profile', 'display_name' => 'get_profile.php', 'relative_path' => 'lab2/api/get_profile.php'],
            ['id' => 'get_users', 'display_name' => 'get_users.php', 'relative_path' => 'lab2/api/get_users.php'],
            ['id' => 'login', 'display_name' => 'login.php', 'relative_path' => 'lab2/api/login.php'],
            ['id' => 'ping', 'display_name' => 'ping.php', 'relative_path' => 'lab2/api/ping.php'],
            ['id' => 'register', 'display_name' => 'register.php', 'relative_path' => 'lab2/api/register.php'],
            ['id' => 'seed_default_user', 'display_name' => 'seed_default_user.php', 'relative_path' => 'lab2/api/seed_default_user.php'],
            ['id' => 'update_email', 'display_name' => 'update_email.php', 'relative_path' => 'lab2/api/update_email.php'],
        ],
    ];
}

function hackme_whitebox_lab19_meta_json(): string
{
    return json_encode(hackme_whitebox_lab19_meta(), JSON_UNESCAPED_SLASHES);
}
