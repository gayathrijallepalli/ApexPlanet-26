<?php
require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');

requireLogin();
requireCsrf();

$id = (int) ($_POST['notification_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];

$db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?')->execute([$id, $userId]);
echo json_encode(['success' => true]);
