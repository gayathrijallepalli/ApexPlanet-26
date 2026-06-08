<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole(['recruiter']);

$userId = (int) $_SESSION['user_id'];
$jobId = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM jobs WHERE id = ? AND posted_by = ?');
$stmt->execute([$jobId, $userId]);
$job = $stmt->fetch();
if (!$job) {
    setFlash('danger', 'Job not found.');
    redirect(url('recruiter/jobs/index.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    if (isset($_POST['delete'])) {
        $db->prepare('DELETE FROM jobs WHERE id = ? AND posted_by = ?')->execute([$jobId, $userId]);
        setFlash('success', 'Job deleted.');
        redirect(url('recruiter/jobs/index.php'));
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $jobType = $_POST['job_type'] ?? 'full-time';
    $experience = trim($_POST['experience_level'] ?? '');
    $salaryMin = $_POST['salary_min'] !== '' ? (float) $_POST['salary_min'] : null;
    $salaryMax = $_POST['salary_max'] !== '' ? (float) $_POST['salary_max'] : null;
    $status = $_POST['status'] ?? 'active';

    $stmt = $db->prepare(
        'UPDATE jobs SET title=?, description=?, requirements=?, location=?, job_type=?, experience_level=?, salary_min=?, salary_max=?, status=? WHERE id=? AND posted_by=?'
    );
    $stmt->execute([$title, $description, $requirements, $location, $jobType, $experience, $salaryMin, $salaryMax, $status, $jobId, $userId]);
    setFlash('success', 'Job updated.');
    redirect(url('recruiter/jobs/index.php'));
}

$pageTitle = 'Edit Job';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
include __DIR__ . '/_form.php';
?>
<div class="glass-card border-danger border-opacity-10 mt-4 p-4 d-flex justify-content-between align-items-center flex-wrap gap-3" style="background: linear-gradient(135deg, rgba(15,23,42,0.95), rgba(239,68,68,0.02));">
    <div>
        <h6 class="fw-bold text-danger mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Danger Zone</h6>
        <p class="text-muted small mb-0">Permanently remove this job posting and delete all associated applicant files.</p>
    </div>
    <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this job? This action cannot be undone.')">
        <?= csrfField() ?>
        <button type="submit" name="delete" value="1" class="btn btn-danger hover-lift"><i class="bi bi-trash-fill me-2"></i>Delete Posting</button>
    </form>
</div>
<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
