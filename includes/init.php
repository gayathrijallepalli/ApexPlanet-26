<?php
$configDir = dirname(__DIR__) . '/config';
if (!file_exists($configDir . '/app.php')) {
    copy($configDir . '/app.example.php', $configDir . '/app.php');
}
require_once $configDir . '/app.php';
require_once $configDir . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

require_once BASE_PATH . '/includes/helpers.php';
require_once BASE_PATH . '/includes/csrf.php';
require_once BASE_PATH . '/includes/auth.php';

$db = Database::getConnection();
