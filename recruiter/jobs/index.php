<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['recruiter']);

$userId = (int) $_SESSION['user_id'];
$stmt = $db->prepare(
    'SELECT j.*, COUNT(a.id) AS applicant_count
     FROM jobs j
     LEFT JOIN applications a ON a.job_id = j.id
     WHERE j.posted_by = ? GROUP BY j.id ORDER BY j.created_at DESC'
);
$stmt->execute([$userId]);
$jobs = $stmt->fetchAll();

$pageTitle = 'My Postings';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="page-heading">My Postings</h1>
        <p class="page-subtitle">Configure, edit, and track applicants for your published opportunities.</p>
    </div>
    <a href="<?= url('recruiter/jobs/create.php') ?>" class="btn btn-primary btn-gradient hover-lift"><i class="bi bi-plus-circle me-2"></i>Post New Job</a>
</div>

<?php if (!$jobs): ?>
    <div class="glass-card text-center py-5">
        <div class="text-muted mb-3"><i class="bi bi-briefcase-fill" style="font-size: 3rem; opacity: 0.3;"></i></div>
        <h5 class="fw-bold">No Jobs Published Yet</h5>
        <p class="text-muted mb-4">Start publishing roles to attract exceptional candidates.</p>
        <a href="<?= url('recruiter/jobs/create.php') ?>" class="btn btn-primary btn-gradient hover-lift">Create First Job Post</a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($jobs as $job): 
            $status = $job['status'];
            $statusClass = $status === 'active' ? 'bg-success' : ($status === 'draft' ? 'bg-warning' : 'bg-muted');
        ?>
            <div class="col-md-6 col-xl-4">
                <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between transition-base hover-lift">
                    <div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <span class="badge bg-white bg-opacity-5 text-muted border border-white border-opacity-5 rounded-pill px-3 py-1" style="font-size:0.75rem;">
                                <?= ucfirst(str_replace('-', ' ', $job['job_type'])) ?>
                            </span>
                            <span class="badge <?= $statusClass ?> bg-opacity-15 text-white border-0 px-2.5 py-1 rounded-2" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.05em;">
                                <?= $status ?>
                            </span>
                        </div>

                        <h5 class="fw-bold mb-1"><a href="<?= url('recruiter/jobs/applicants.php?job_id=' . $job['id']) ?>" class="text-decoration-none text-white"><?= e($job['title']) ?></a></h5>
                        <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1"></i><?= e($job['location']) ?></p>

                        <div class="d-flex gap-2 flex-wrap mb-4">
                            <?php if ($job['experience_level']): ?>
                                <span class="badge bg-white bg-opacity-5 text-muted border border-white border-opacity-5 rounded-pill px-2.5 py-1" style="font-size:0.7rem;">
                                    <i class="bi bi-person me-1 text-primary"></i><?= e($job['experience_level']) ?>
                                </span>
                            <?php endif; ?>
                            <span class="badge bg-white bg-opacity-5 text-muted border border-white border-opacity-5 rounded-pill px-2.5 py-1" style="font-size:0.7rem;">
                                <i class="bi bi-cash me-1 text-success"></i><?= formatSalary($job['salary_min'], $job['salary_max']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top border-white border-opacity-5 mt-auto">
                        <a href="<?= url('recruiter/jobs/applicants.php?job_id=' . $job['id']) ?>" class="d-flex align-items-center gap-2 text-decoration-none" title="Process applicants">
                            <div class="rounded-circle bg-primary bg-opacity-15 text-primary d-flex align-items-center justify-content-center fw-bold" style="width:34px; height:34px; font-size:0.85rem;">
                                <?= $job['applicant_count'] ?>
                            </div>
                            <span class="text-muted small hover-glow">Candidates</span>
                        </a>
                        <div class="d-flex gap-2">
                            <a href="<?= url('recruiter/jobs/edit.php?id=' . $job['id']) ?>" class="btn btn-sm btn-outline-primary hover-lift"><i class="bi bi-pencil-square"></i></a>
                            <a href="<?= url('recruiter/jobs/applicants.php?job_id=' . $job['id']) ?>" class="btn btn-sm btn-primary hover-lift px-3">Review</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
