CTF Platform - Email verification PHP backend
Files included:
- ctf_platform.sql            : SQL script to create the database and tables (includes email_verifications)
- db_connect.php              : DB connection (edit DB credentials if needed)
- send_verification.php      : POST JSON { username, email } -> sends 6-digit code via PHPMailer (or mail())
- verify_code.php            : POST JSON { email, code } -> verifies code
- set_password.php           : POST JSON { username, email, fullName, password } -> finalizes registration

Setup:
1) Place this folder under your XAMPP htdocs, e.g. C:\xampp\htdocs\ctf_auth
2) Import ctf_platform.sql into phpMyAdmin (or run it using mysql client)
3) Install PHPMailer via Composer (recommended):
   - Install Composer if not present: https://getcomposer.org/download/
   - In the project folder run: composer require phpmailer/phpmailer
   - This creates vendor/autoload.php used by send_verification.php
   If you don't use Composer, send_verification.php will try PHP mail() as fallback.
4) Configure Gmail app password:
   - Use your Gmail account -> create an App Password (Mail) and copy it
   - Edit send_verification.php: replace YOUR_EMAIL@gmail.com and YOUR_APP_PASSWORD with your credentials
5) Start XAMPP (Apache + MySQL) and ensure PHP mail or PHPMailer SMTP works
6) Test with curl or Postman:
   - Send verification:
     curl -X POST http://localhost/ctf_auth/send_verification.php -H "Content-Type: application/json" -d '{ "username":"abdo","email":"you@example.com" }'
   - Verify:
     curl -X POST http://localhost/ctf_auth/verify_code.php -H "Content-Type: application/json" -d '{ "email":"you@example.com","code":"123456" }'
   - Set password:
     curl -X POST http://localhost/ctf_auth/set_password.php -H "Content-Type: application/json" -d '{ "username":"abdo","email":"you@example.com","fullName":"Abdo","password":"secret123" }'

Security notes:
- Replace placeholders with real credentials and never commit them to public repos.
- Use HTTPS in production.
- Consider rate-limiting and protecting endpoints against brute-force attempts.
