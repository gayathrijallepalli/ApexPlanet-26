<?php
require_once __DIR__ . '/../includes/init.php';
require_once BASE_PATH . '/services/ApplicationService.php';
header('Content-Type: application/json');

requireLogin();
requireRole(['recruiter']);
requireCsrf();

$applicationId = (int) ($_POST['application_id'] ?? 0);
$status = $_POST['status'] ?? '';
$notes = trim($_POST['notes'] ?? '');

$result = ApplicationService::updateStatus($db, $applicationId, (int) $_SESSION['user_id'], $status, $notes);
echo json_encode($result);
