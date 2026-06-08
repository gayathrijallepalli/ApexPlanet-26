<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['jobseeker']);

$userId = (int) $_SESSION['user_id'];
$stmt = $db->prepare(
    'SELECT j.*, c.name AS company_name, c.logo, s.saved_at
     FROM saved_jobs s
     JOIN jobs j ON j.id = s.job_id
     JOIN companies c ON c.id = j.company_id
     WHERE s.user_id = ? ORDER BY s.saved_at DESC'
);
$stmt->execute([$userId]);
$jobs = $stmt->fetchAll();

$pageTitle = 'Saved Opportunities';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="mb-4">
    <h1 class="page-heading">Saved Opportunities</h1>
    <p class="page-subtitle">Manage listings you bookmarked for later review or applications.</p>
</div>

<div class="row g-4">
    <?php if (!$jobs): ?>
        <div class="col-12">
            <div class="glass-card text-center py-5">
                <div class="text-muted mb-3"><i class="bi bi-bookmark-fill" style="font-size: 3rem; opacity: 0.3;"></i></div>
                <h5 class="fw-bold">No Bookmarks Found</h5>
                <p class="text-muted mb-4">You haven't saved any jobs yet.</p>
                <a href="<?= url('jobseeker/jobs.php') ?>" class="btn btn-primary btn-gradient hover-lift">Browse Active Jobs</a>
            </div>
        </div>
    <?php else: foreach ($jobs as $job): ?>
        <div class="col-md-6 col-xl-4">
            <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between transition-base hover-lift">
                <div>
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center text-white fw-bold" 
                             style="width: 42px; height: 42px; background: var(--grad-blue-cyan); font-size: 1.1rem; flex-shrink: 0;">
                            <?= strtoupper(substr($job['company_name'], 0, 1)) ?>
                        </div>
                        <button class="btn btn-sm btn-outline-danger border-0 bg-white bg-opacity-5 hover-lift btn-unsave-job" data-job-id="<?= $job['id'] ?>" title="Remove bookmark">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>

                    <h5 class="fw-bold mb-1"><a href="<?= url('jobseeker/job-detail.php?id=' . $job['id']) ?>" class="text-decoration-none text-white"><?= e($job['title']) ?></a></h5>
                    <p class="text-muted small mb-3"><?= e($job['company_name']) ?> &middot; <?= e($job['location']) ?></p>

                    <div class="d-flex gap-2 flex-wrap mb-4">
                        <span class="badge bg-white bg-opacity-5 text-muted border border-white border-opacity-5 rounded-pill px-2.5 py-1" style="font-size:0.7rem;">
                            <i class="bi bi-briefcase me-1 text-primary"></i><?= ucfirst($job['job_type']) ?>
                        </span>
                        <?php if ($job['experience_level']): ?>
                            <span class="badge bg-white bg-opacity-5 text-muted border border-white border-opacity-5 rounded-pill px-2.5 py-1" style="font-size:0.7rem;">
                                <i class="bi bi-person me-1 text-secondary"></i><?= e($job['experience_level']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-white border-opacity-5">
                    <span class="text-muted" style="font-size:0.7rem;"><i class="bi bi-calendar-check me-1"></i>Saved <?= timeAgo($job['saved_at']) ?></span>
                    <a href="<?= url('jobseeker/job-detail.php?id=' . $job['id']) ?>" class="btn btn-sm btn-primary hover-lift px-3">Apply <i class="bi bi-arrow-right-short ms-1"></i></a>
                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-unsave-job').forEach(btn => {
        btn.addEventListener('click', function() {
            const jobId = this.dataset.jobId;
            const cardCol = this.closest('.col-md-6');
            
            fetch(window.SHP_BASE_URL + '/ajax/save_job.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': '<?= csrfToken() ?>',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'job_id=' + jobId + '&csrf_token=<?= csrfToken() ?>'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (window.showToast) window.showToast('Bookmark removed.', 'success');
                    
                    // Smoothly remove card from UI
                    cardCol.style.transition = 'transform 0.4s ease, opacity 0.4s ease';
                    cardCol.style.transform = 'scale(0.9)';
                    cardCol.style.opacity = '0';
                    setTimeout(() => {
                        cardCol.remove();
                        // Check if no jobs left
                        if (document.querySelectorAll('.btn-unsave-job').length === 0) {
                            window.location.reload();
                        }
                    }, 400);
                } else {
                    if (window.showToast) window.showToast('Failed to remove bookmark.', 'danger');
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showToast) window.showToast('Network error, please try again.', 'danger');
            });
        });
    });
});
</script>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
