<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $jobId = (int) ($_POST['job_id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete') {
        $db->prepare('DELETE FROM jobs WHERE id = ?')->execute([$jobId]);
        logAdminAction($db, (int) $_SESSION['user_id'], 'delete_job', 'job', $jobId);
        setFlash('success', 'Job posting deleted.');
    } elseif (isset($_POST['status'])) {
        $db->prepare('UPDATE jobs SET status = ? WHERE id = ?')->execute([$_POST['status'], $jobId]);
        logAdminAction($db, (int) $_SESSION['user_id'], 'update_job_status', 'job', $jobId);
        setFlash('success', 'Job status updated.');
    }
    redirect(url('admin/jobs.php'));
}

$stmt = $db->query(
    'SELECT j.*, c.name AS company_name, u.full_name AS posted_by_name
     FROM jobs j JOIN companies c ON c.id = j.company_id JOIN users u ON u.id = j.posted_by
     ORDER BY j.created_at DESC'
);
$jobs = $stmt->fetchAll();

$pageTitle = 'Platform Jobs';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="mb-4">
    <h1 class="page-heading">Platform Jobs</h1>
    <p class="page-subtitle">Inspect job listings, modify active status toggles, or delete obsolete posts.</p>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="glass-card p-4">
            <h5 class="section-title mb-4"><i class="bi bi-briefcase-fill text-primary me-2"></i>Job Catalog</h5>
            
            <?php if (empty($jobs)): ?>
                <p class="text-muted mb-0">No job openings published yet.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($jobs as $job): 
                        $initials = strtoupper(substr($job['company_name'], 0, 1) . (strpos($job['company_name'], ' ') ? substr(explode(' ', $job['company_name'])[1], 0, 1) : ''));
                        $status = $job['status'];
                        $statusClass = $status === 'active' ? 'bg-success' : ($status === 'draft' ? 'bg-warning' : 'bg-muted');
                    ?>
                        <div class="p-3 bg-white bg-opacity-5 rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-3 transition-base hover-lift border-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" 
                                     style="width: 44px; height: 44px; font-size:1.1rem; background: var(--grad-blue-cyan); color: #fff !important; flex-shrink: 0;">
                                    <?= $initials ?>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-white fw-bold"><?= e($job['title']) ?></h6>
                                    <p class="text-muted small mb-0">
                                        <span><i class="bi bi-building me-1"></i><?= e($job['company_name']) ?></span> &middot; 
                                        <span><i class="bi bi-person me-1"></i>Posted by <?= e($job['posted_by_name']) ?></span> &middot; 
                                        <span><i class="bi bi-calendar-event me-1"></i><?= formatDate($job['created_at']) ?></span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <?= statusBadge($status) ?>
                                
                                <form method="POST" class="d-flex align-items-center gap-2">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" style="width: 110px;" onchange="this.form.submit()">
                                        <?php foreach (['active','draft','closed'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $job['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger hover-lift" onclick="return confirm('Confirm permanent deletion of this job posting?')"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
