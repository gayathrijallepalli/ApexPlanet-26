<?php
require_once __DIR__ . '/../includes/init.php';
require_once BASE_PATH . '/services/ApplicationService.php';
header('Content-Type: application/json');

requireLogin();
requireRole(['jobseeker']);
requireCsrf();

$jobId = (int) ($_POST['job_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];
$cover = trim($_POST['cover_letter'] ?? '');

$result = ApplicationService::apply($db, $userId, $jobId, $cover);
echo json_encode($result);
