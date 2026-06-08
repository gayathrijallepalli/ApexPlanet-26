<?php
require_once __DIR__ . '/../includes/init.php';
require_once BASE_PATH . '/services/NotificationService.php';
header('Content-Type: application/json');

requireLogin();

$userId = (int) $_SESSION['user_id'];
$notifications = NotificationService::getRecent($db, $userId, 8);

echo json_encode(['success' => true, 'notifications' => $notifications]);
