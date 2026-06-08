# Deployment Guide — SmartHire Pro

Deploy to **InfinityFree** or **000WebHost** free hosting.

## InfinityFree Deployment

### 1. Create Account & Website

1. Sign up at [infinityfree.net](https://infinityfree.net)
2. Create a new website (subdomain or custom domain)
3. Note your FTP credentials and control panel URL

### 2. Create MySQL Database

1. Open **MySQL Databases** in control panel
2. Create a new database
3. Note: hostname, database name, username, password

### 3. Upload Files

Upload all project files to `htdocs` via:
- **File Manager** in control panel, or
- **FTP** (FileZilla)

Exclude from upload (optional):
- `.git/`
- `install.php` (after running once)

### 4. Import Database

1. Open **phpMyAdmin** from control panel
2. Select your database
3. Import `database/schema.sql`
4. Run admin seed via install.php OR manually insert admin user

### 5. Configure Application

Edit `config/app.php` on the server:

```php
define('BASE_URL', 'https://yoursite.infinityfreeapp.com');
define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_NAME', 'if0_XXXXXXX_smarthire');
define('DB_USER', 'if0_XXXXXXX');
define('DB_PASS', 'your-db-password');
define('DEV_MODE', false);

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
```

### 6. Set Folder Permissions

Ensure these directories are writable:
- `uploads/resumes/`
- `uploads/photos/`
- `uploads/logos/`
- `logs/`

Set permissions to **755** or **775** via File Manager.

### 7. Install Composer Dependencies

If SSH is unavailable, upload the `vendor/` folder from local after running `composer install`.

### 8. Post-Deployment

- [ ] Delete `install.php`
- [ ] Change admin password
- [ ] Test registration, OTP email, job apply flow
- [ ] Verify file uploads work

## GitHub Setup

```bash
cd C:\xampp\htdocs\Task-5
git init
git add .
git commit -m "Initial commit: SmartHire Pro recruitment platform"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/smarthire-pro.git
git push -u origin main
```

Add topics: `php`, `mysql`, `recruitment`, `bootstrap5`, `capstone`

## Known Hosting Limitations

| Limitation | Workaround |
|------------|------------|
| Email rate limits | Add 60s OTP resend cooldown |
| No cron jobs | Use on-demand triggers |
| File size limits | Keep resume max at 5MB |
| No Composer CLI | Upload vendor folder locally |

## Environment Comparison

| Setting | Local (XAMPP) | Production |
|---------|---------------|------------|
| BASE_URL | http://localhost/Task-5 | https://yourdomain.com |
| DEV_MODE | true | false |
| DB_USER | root | hosting user |
| SMTP | optional | required for OTP |
