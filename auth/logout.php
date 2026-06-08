<?php
require_once __DIR__ . '/../includes/init.php';
session_destroy();
session_start();
setFlash('success', 'You have been logged out.');
redirect(url('auth/login.php'));
