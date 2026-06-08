<?php
require_once __DIR__ . '/../includes/init.php';
require_once BASE_PATH . '/services/ApplicationService.php';
header('Content-Type: application/json');

requireLogin();
requireRole(['jobseeker']);
requireCsrf();

$jobId = (int) ($_POST['job_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];

if (isset($_POST['action']) && $_POST['action'] === 'unsave') {
    $db->prepare('DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?')->execute([$userId, $jobId]);
    echo json_encode(['success' => true, 'saved' => false]);
    exit;
}

$stmt = $db->prepare('SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?');
$stmt->execute([$userId, $jobId]);
if ($stmt->fetch()) {
    $db->prepare('DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?')->execute([$userId, $jobId]);
    echo json_encode(['success' => true, 'saved' => false]);
    exit;
}

$db->prepare('INSERT INTO saved_jobs (user_id, job_id) VALUES (?, ?)')->execute([$userId, $jobId]);
logActivity($db, $userId, 'save_job', "Saved job #{$jobId}");
echo json_encode(['success' => true, 'saved' => true]);
