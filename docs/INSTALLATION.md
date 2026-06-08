# Installation Guide — SmartHire Pro

## Prerequisites

- **XAMPP** with PHP 8+ and MySQL 8+
- Web browser (Chrome, Firefox, Edge)
- Optional: **Composer** for PHPMailer

## Step 1: Place Project Files

Copy the project folder to your XAMPP htdocs directory:

```
C:\xampp\htdocs\Task-5
```

## Step 2: Start XAMPP Services

1. Open XAMPP Control Panel
2. Start **Apache**
3. Start **MySQL**

## Step 3: Run Installer

Open in browser:

```
http://localhost/Task-5/install.php
```

The installer will:
- Create the `smarthire_pro` database
- Import all tables
- Create the admin user
- Create upload directories
- Generate `config/app.php`

## Step 4: Configure (Optional)

Edit `config/app.php`:

```php
define('BASE_URL', 'http://localhost/Task-5');
define('DB_HOST', 'localhost');
define('DB_NAME', 'smarthire_pro');
define('DB_USER', 'root');
define('DB_PASS', '');
```

For production email, set SMTP credentials and `DEV_MODE` to `false`.

## Step 5: Install PHPMailer (Optional)

```bash
composer install
```

In dev mode (`DEV_MODE = true`), OTP codes appear on screen and in `logs/otp.log`.

## Step 6: Verify Installation

1. Visit `http://localhost/Task-5/`
2. Login as admin: `admin@smarthire.pro` / `Admin@123`
3. Register a job seeker and recruiter to test flows

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Connection failed | Ensure MySQL is running; check DB credentials in `config/app.php` |
| Blank page | Enable errors in `php.ini`: `display_errors = On` |
| Upload fails | Check `uploads/` folder permissions; increase `upload_max_filesize` in php.ini |
| OTP not received | Use dev mode OTP on screen, or configure SMTP |

## php.ini Settings (Recommended)

```ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 120
```

## Security Checklist (Production)

- [ ] Delete `install.php`
- [ ] Set `DEV_MODE` to `false`
- [ ] Change admin password
- [ ] Use HTTPS
- [ ] Disable `display_errors`
