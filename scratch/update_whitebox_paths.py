import re

# 1. Update whitebox_lab18_defaults.php
f1 = r'c:\xampp\htdocs\HackMe\server\helpers\whitebox\whitebox_lab18_defaults.php'
with open(f1, 'r', encoding='utf-8') as f:
    c1 = f.read()

c1 = c1.replace("'public/admin_panel.php'", "'lab1/api/update_role.php'")
c1 = c1.replace("'public/index.php'", "'lab1/api/login.php'")
c1 = c1.replace("'includes/auth_bootstrap.php'", "'lab1/api/admin_users.php'")

c1 = re.sub(
    r"'files' => \[.*?\];",
    r"""'files' => [
            [
                'id' => 'update_role',
                'display_name' => 'update_role.php',
                'relative_path' => 'lab1/api/update_role.php',
                'vulnerable_line' => 27,
            ],
            [
                'id' => 'login',
                'display_name' => 'login.php',
                'relative_path' => 'lab1/api/login.php',
            ],
            [
                'id' => 'admin_users',
                'display_name' => 'admin_users.php',
                'relative_path' => 'lab1/api/admin_users.php',
            ],
            [
                'id' => 'config',
                'display_name' => 'config.php',
                'relative_path' => 'lab1/api/config.php',
            ],
        ],""",
    c1, flags=re.DOTALL
)
with open(f1, 'w', encoding='utf-8') as f:
    f.write(c1)

# 2. Update whitebox_lab19_defaults.php
f2 = r'c:\xampp\htdocs\HackMe\server\helpers\whitebox\whitebox_lab19_defaults.php'
with open(f2, 'r', encoding='utf-8') as f:
    c2 = f.read()

c2 = c2.replace("'public/user_profile.php'", "'lab2/api/get_profile.php'")
c2 = c2.replace("'public/lab19_entry.php'", "'lab2/api/update_email.php'")
c2 = c2.replace("'includes/lab19_scaffold.php'", "'lab2/api/get_users.php'")

c2 = re.sub(
    r"'files' => \[.*?\];",
    r"""'files' => [
            [
                'id' => 'get_profile',
                'display_name' => 'get_profile.php',
                'relative_path' => 'lab2/api/get_profile.php',
                'vulnerable_line' => 17,
            ],
            [
                'id' => 'update_email',
                'display_name' => 'update_email.php',
                'relative_path' => 'lab2/api/update_email.php',
            ],
            [
                'id' => 'get_users',
                'display_name' => 'get_users.php',
                'relative_path' => 'lab2/api/get_users.php',
            ],
            [
                'id' => 'login',
                'display_name' => 'login.php',
                'relative_path' => 'lab2/api/login.php',
            ],
        ],""",
    c2, flags=re.DOTALL
)
with open(f2, 'w', encoding='utf-8') as f:
    f.write(c2)

# 3. Update whitebox_xss_defaults.php
f3 = r'c:\xampp\htdocs\HackMe\server\helpers\whitebox\whitebox_xss_defaults.php'
with open(f3, 'r', encoding='utf-8') as f:
    c3 = f.read()

c3 = re.sub(
    r"'files' => \[\s*\[\s*'id' => 'search',\s*'display_name' => 'search\.php',\s*'relative_path' => 'search\.php',\s*'vulnerable_line' => 6,\s*\],\s*\];",
    r"""'files' => [
            [
                'id' => 'index',
                'display_name' => 'index.php',
                'relative_path' => 'index.php',
                'vulnerable_line' => 87,
            ],
        ];""",
    c3, flags=re.DOTALL
)

c3 = re.sub(
    r"'files' => \[\s*\[\s*'id' => 'appjs',\s*'display_name' => 'app\.js',\s*'relative_path' => 'app\.js',\s*'vulnerable_line' => 4,\s*\],\s*\];",
    r"""'files' => [
            [
                'id' => 'index_html',
                'display_name' => 'index.html',
                'relative_path' => 'index.html',
                'vulnerable_line' => 370,
            ],
        ];""",
    c3, flags=re.DOTALL
)
with open(f3, 'w', encoding='utf-8') as f:
    f.write(c3)

print("Done updating whitebox defaults")
