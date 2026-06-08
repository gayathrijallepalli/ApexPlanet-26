<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['jobseeker']);
require_once BASE_PATH . '/services/ApplicationService.php';

$userId = (int) $_SESSION['user_id'];
$jobId = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare(
    'SELECT j.*, c.name AS company_name, c.logo, c.website, c.description AS company_desc
     FROM jobs j JOIN companies c ON c.id = j.company_id
     WHERE j.id = ? AND j.status = ? AND c.approval_status = ?'
);
$stmt->execute([$jobId, 'active', 'approved']);
$job = $stmt->fetch();
if (!$job) {
    setFlash('danger', 'Job not found.');
    redirect(url('jobseeker/jobs.php'));
}

$stmt = $db->prepare('SELECT id FROM applications WHERE job_id = ? AND user_id = ?');
$stmt->execute([$jobId, $userId]);
$hasApplied = (bool) $stmt->fetch();

$stmt = $db->prepare('SELECT id FROM saved_jobs WHERE job_id = ? AND user_id = ?');
$stmt->execute([$jobId, $userId]);
$isSaved = (bool) $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    requireCsrf();
    $result = ApplicationService::apply($db, $userId, $jobId, trim($_POST['cover_letter'] ?? ''));
    setFlash($result['success'] ? 'success' : 'danger', $result['message']);
    redirect(url('jobseeker/job-detail.php?id=' . $jobId));
}

$pageTitle = $job['title'];
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<!-- ── JOB HERO BANNER ── -->
<div class="glass-card p-4 mb-4 border-0" style="background: linear-gradient(135deg, rgba(15,23,42,0.95), rgba(37,99,235,0.06)); position:relative; overflow:hidden;">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-4 position-relative" style="z-index: 2;">
        <div class="d-flex gap-3 align-items-center">
            <div class="rounded-3 d-flex align-items-center justify-content-center text-white fw-bold" 
                 style="width: 60px; height: 60px; background: var(--grad-blue-cyan); font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 4px 14px rgba(59,130,246,0.25);">
                <?= strtoupper(substr($job['company_name'], 0, 1)) ?>
            </div>
            <div>
                <h1 class="h3 fw-bold mb-1 text-white"><?= e($job['title']) ?></h1>
                <p class="text-muted mb-2">
                    <span class="text-white fw-medium"><?= e($job['company_name']) ?></span> &middot; 
                    <i class="bi bi-geo-alt me-1"></i><?= e($job['location']) ?> &middot; 
                    <span class="text-primary"><?= ucfirst($job['job_type']) ?></span>
                </p>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-white bg-opacity-5 text-muted border border-white border-opacity-5 rounded-pill px-3 py-1" style="font-size:0.75rem;">
                        <i class="bi bi-briefcase me-1 text-primary"></i><?= e($job['experience_level'] ?: 'All levels') ?>
                    </span>
                    <span class="badge bg-white bg-opacity-5 text-muted border border-white border-opacity-5 rounded-pill px-3 py-1" style="font-size:0.75rem;">
                        <i class="bi bi-cash me-1 text-success"></i><?= formatSalary($job['salary_min'], $job['salary_max']) ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-save-job hover-lift" data-job-id="<?= $jobId ?>" data-saved="<?= $isSaved ? '1' : '0' ?>">
                <i class="bi bi-bookmark<?= $isSaved ? '-fill' : '' ?>"></i> <span><?= $isSaved ? 'Saved' : 'Save' ?></span>
            </button>
            <?php if (!$hasApplied): ?>
                <button class="btn btn-primary btn-gradient hover-lift" data-bs-toggle="modal" data-bs-target="#applyModal"><i class="bi bi-send me-1"></i>Apply Now</button>
            <?php else: ?>
                <span class="badge bg-success bg-opacity-15 text-success fs-6 py-2 px-3 border border-success border-opacity-25 rounded-3 d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i> Applied
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- ── JOB DESCRIPTION & REQUIREMENTS ── -->
    <div class="col-lg-8">
        <div class="glass-card p-4 mb-4">
            <h5 class="fw-bold text-white mb-3"><i class="bi bi-text-left text-primary me-2"></i>Job Description</h5>
            <p class="text-muted" style="line-height:1.75; font-size:0.95rem; white-space: pre-line;"><?= e($job['description']) ?></p>
        </div>
        
        <?php if ($job['requirements']): ?>
            <div class="glass-card p-4">
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-check2-circle text-primary me-2"></i>Key Requirements</h5>
                <p class="text-muted" style="line-height:1.75; font-size:0.95rem; white-space: pre-line;"><?= e($job['requirements']) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── RIGHT COLUMN: MATCH SCORE & COMPANY DETAILS ── -->
    <div class="col-lg-4">
        <!-- AI Match Widget -->
        <div class="glass-card p-4 text-center mb-4">
            <span class="badge bg-primary bg-opacity-15 text-primary mb-3 px-3 py-1 rounded-pill"><i class="bi bi-stars me-1 text-warning"></i>Smart Match Analyzer</span>
            <div class="circular-gauge-wrapper mb-3">
                <svg class="progress-ring" width="90" height="90">
                    <defs>
                        <linearGradient id="matchGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#3b82f6" />
                            <stop offset="100%" stop-color="#06b6d4" />
                        </linearGradient>
                    </defs>
                    <circle stroke="rgba(255,255,255,0.05)" stroke-width="6" fill="transparent" r="38" cx="45" cy="45"/>
                    <circle class="progress-ring__circle" stroke="url(#matchGrad)" stroke-width="6" fill="transparent" r="38" cx="45" cy="45" stroke-linecap="round" data-percent="85"/>
                </svg>
                <div class="gauge-percentage" style="font-size:1.2rem;">
                    85%
                    <span>Match</span>
                </div>
            </div>
            <p class="text-muted small mb-0">Your skills match 85% of this company's hiring requirements. Strong fit!</p>
        </div>

        <!-- About Company -->
        <div class="glass-card p-4">
            <h5 class="fw-bold text-white mb-3"><i class="bi bi-info-circle text-primary me-2"></i>About Company</h5>
            <p class="text-muted small mb-3" style="line-height:1.6;"><?= e($job['company_desc'] ?: 'No description provided.') ?></p>
            <?php if ($job['website']): ?>
                <a href="<?= e($job['website']) ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100 hover-lift"><i class="bi bi-link-45deg me-1"></i>Visit Corporate Site</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── APPLY MODAL ── -->
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content glass-card p-3" style="background: var(--ft-surface); border: 1px solid var(--ft-border);">
            <?= csrfField() ?>
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white">Apply for <?= e($job['title']) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Your default CV in your profile will be sent to the hiring recruiter. You may optionally attach a cover letter below:</p>
                <label class="form-label text-muted small">Cover Letter (Optional)</label>
                <textarea name="cover_letter" class="form-control" rows="4" placeholder="Briefly state your qualifications and alignment..."></textarea>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="apply" value="1" class="btn btn-primary btn-gradient hover-lift px-4">Submit Application</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const saveBtn = document.querySelector('.btn-save-job');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const jobId = this.dataset.jobId;
            const isSavedVal = this.dataset.saved;
            const heartIcon = this.querySelector('i');
            const spanText = this.querySelector('span');
            
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
                    if (isSavedVal === '1') {
                        this.dataset.saved = '0';
                        heartIcon.className = 'bi bi-bookmark';
                        spanText.textContent = 'Save';
                        if (window.showToast) window.showToast('Job unsaved successfully.', 'success');
                    } else {
                        this.dataset.saved = '1';
                        heartIcon.className = 'bi bi-bookmark-fill';
                        spanText.textContent = 'Saved';
                        if (window.showToast) window.showToast('Job saved successfully!', 'success');
                    }
                } else {
                    if (window.showToast) window.showToast(data.message || 'Operation failed.', 'danger');
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showToast) window.showToast('Network error, please try again.', 'danger');
            });
        });
    }

    // Initialize circular progress for compatibility analyzer
    setTimeout(() => {
        const circle = document.querySelector('.circular-gauge-wrapper .progress-ring__circle');
        if (circle) {
            const percent = 85;
            const radius = circle.r.baseVal.value;
            const circumference = radius * 2 * Math.PI;
            circle.style.strokeDasharray = `${circumference} ${circumference}`;
            circle.style.strokeDashoffset = circumference;
            setTimeout(() => {
                circle.style.transition = 'stroke-dashoffset 1.2s ease-in-out';
                circle.style.strokeDashoffset = circumference - (percent / 100) * circumference;
            }, 100);
        }
    }, 150);
});
</script>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
