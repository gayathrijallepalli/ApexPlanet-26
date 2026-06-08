<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole(['recruiter']);

$userId = (int) $_SESSION['user_id'];
$stmt = $db->prepare('SELECT * FROM companies WHERE recruiter_id = ? AND approval_status = ?');
$stmt->execute([$userId, 'approved']);
$company = $stmt->fetch();

if (!$company) {
    setFlash('warning', 'Your company must be approved before posting jobs.');
    redirect(url('recruiter/company.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $jobType = $_POST['job_type'] ?? 'full-time';
    $experience = trim($_POST['experience_level'] ?? '');
    $salaryMin = $_POST['salary_min'] !== '' ? (float) $_POST['salary_min'] : null;
    $salaryMax = $_POST['salary_max'] !== '' ? (float) $_POST['salary_max'] : null;
    $status = $_POST['status'] ?? 'active';

    if ($title && $description && $location) {
        $stmt = $db->prepare(
            'INSERT INTO jobs (company_id, posted_by, title, description, requirements, location, job_type, experience_level, salary_min, salary_max, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$company['id'], $userId, $title, $description, $requirements, $location, $jobType, $experience, $salaryMin, $salaryMax, $status]);
        logActivity($db, $userId, 'create_job', "Created job: {$title}");
        setFlash('success', 'Job posted successfully.');
        redirect(url('recruiter/jobs/index.php'));
    }
    setFlash('danger', 'Please fill required fields.');
}

$pageTitle = 'Post New Job';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
include __DIR__ . '/_form.php';
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
