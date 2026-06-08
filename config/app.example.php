<?php
define('APP_NAME', 'SmartHire Pro');
define('BASE_URL', 'http://localhost/Task-5');
define('BASE_PATH', dirname(__DIR__));

define('DB_HOST', 'localhost');
define('DB_NAME', 'smarthire_pro');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM', 'noreply@smarthire.pro');
define('SMTP_FROM_NAME', 'SmartHire Pro');

define('DEV_MODE', true);
define('OTP_LOG_FILE', BASE_PATH . '/logs/otp.log');

define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('ALLOWED_RESUME_TYPES', ['application/pdf']);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
