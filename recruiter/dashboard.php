<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['recruiter']);

$userId = (int) $_SESSION['user_id'];

$stmt = $db->prepare('SELECT * FROM companies WHERE recruiter_id = ?');
$stmt->execute([$userId]);
$company = $stmt->fetch();

$jobCount = 0;
$appCount = 0;
$shortlistedCount = 0;
$interviewingCount = 0;
$hiredCount = 0;
$recentApplicants = [];
$topJobs = [];

if ($company) {
    $companyId = (int) $company['id'];
    
    // Stats
    $stmt = $db->prepare('SELECT COUNT(*) FROM jobs WHERE company_id = ?');
    $stmt->execute([$companyId]);
    $jobCount = (int) $stmt->fetchColumn();

    $stmt = $db->prepare(
        'SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.company_id = ?'
    );
    $stmt->execute([$companyId]);
    $appCount = (int) $stmt->fetchColumn();

    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.company_id = ? AND a.status = 'shortlisted'"
    );
    $stmt->execute([$companyId]);
    $shortlistedCount = (int) $stmt->fetchColumn();

    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.company_id = ? AND a.status = 'interview_scheduled'"
    );
    $stmt->execute([$companyId]);
    $interviewingCount = (int) $stmt->fetchColumn();

    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.company_id = ? AND a.status = 'selected'"
    );
    $stmt->execute([$companyId]);
    $hiredCount = (int) $stmt->fetchColumn();

    // Recent applicants feed
    $stmt = $db->prepare(
        "SELECT a.*, u.full_name, u.email, j.title AS job_title, j.id AS job_id
         FROM applications a
         JOIN users u ON u.id = a.user_id
         JOIN jobs j ON j.id = a.job_id
         WHERE j.company_id = ?
         ORDER BY a.applied_at DESC LIMIT 5"
    );
    $stmt->execute([$companyId]);
    $recentApplicants = $stmt->fetchAll();

    // Top jobs by applicant count
    $stmt = $db->prepare(
        'SELECT j.title, j.id, COUNT(a.id) AS applicants
         FROM jobs j LEFT JOIN applications a ON a.job_id = j.id
         WHERE j.company_id = ? GROUP BY j.id ORDER BY applicants DESC LIMIT 5'
    );
    $stmt->execute([$companyId]);
    $topJobs = $stmt->fetchAll();
}

$pageTitle = 'Recruiter Dashboard';
$extraJs = [asset('js/dashboard.js')];
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="page-heading">Recruiter Space</h1>
        <p class="page-subtitle">Welcome, <?= e(explode(' ', $_SESSION['full_name'] ?? 'Recruiter')[0]) ?>. Review candidate pipelines and schedule assessments.</p>
    </div>
    <?php if ($company && $company['approval_status'] === 'approved'): ?>
        <a href="<?= url('recruiter/jobs/create.php') ?>" class="btn btn-primary btn-gradient hover-lift"><i class="bi bi-plus-circle me-2"></i>Post New Opening</a>
    <?php endif; ?>
</div>

<!-- Warning / Pending Alerts -->
<?php if (!$company): ?>
    <div class="alert alert-warning py-3 border-0 bg-warning bg-opacity-10 text-warning rounded-3 mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <span>Your recruiter profile is unlinked. Please <a href="<?= url('recruiter/company.php') ?>" class="fw-bold text-decoration-none">set up your company details</a> to start hiring.</span>
    </div>
<?php elseif ($company['approval_status'] === 'pending'): ?>
    <div class="alert alert-info py-3 border-0 bg-info bg-opacity-10 text-info rounded-3 mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <span>Your company listing is currently pending system verification. We will notify you once approved.</span>
    </div>
<?php elseif ($company['approval_status'] === 'rejected'): ?>
    <div class="alert alert-danger py-3 border-0 bg-danger bg-opacity-10 text-danger rounded-3 mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-x-circle-fill fs-5"></i>
        <span>Your company credentials could not be verified by admin. Please contact support.</span>
    </div>
<?php endif; ?>

<!-- ── STATS ROW ── -->
<div class="row g-4 mb-4">
    <!-- Active Jobs -->
    <div class="col-md-6 col-xl-3">
        <div class="ft-stat-card d-flex align-items-center justify-content-between p-4" style="--card-accent: var(--grad-blue-cyan)">
            <div>
                <span class="stat-number"><?= $jobCount ?></span>
                <p class="stat-label">Active Job Postings</p>
            </div>
            <div class="card-icon" style="background:rgba(6,182,212,0.1); color:var(--ft-secondary);"><i class="bi bi-briefcase"></i></div>
        </div>
    </div>

    <!-- Total Applicants -->
    <div class="col-md-6 col-xl-3">
        <div class="ft-stat-card d-flex align-items-center justify-content-between p-4" style="--card-accent: var(--grad-blue-violet)">
            <div>
                <span class="stat-number"><?= $appCount ?></span>
                <p class="stat-label">Total Applicants</p>
            </div>
            <div class="card-icon" style="background:rgba(139,92,246,0.1); color:#A78BFA;"><i class="bi bi-people"></i></div>
        </div>
    </div>

    <!-- Interviewing -->
    <div class="col-md-6 col-xl-3">
        <div class="ft-stat-card d-flex align-items-center justify-content-between p-4" style="--card-accent: var(--ft-warning); --card-icon-bg: rgba(245,158,11,0.1); --card-icon-color: var(--ft-warning)">
            <div>
                <span class="stat-number"><?= $interviewingCount ?></span>
                <p class="stat-label">Candidacies Interviewing</p>
            </div>
            <div class="card-icon"><i class="bi bi-camera-video"></i></div>
        </div>
    </div>

    <!-- Hired -->
    <div class="col-md-6 col-xl-3">
        <div class="ft-stat-card d-flex align-items-center justify-content-between p-4" style="--card-accent: var(--ft-success); --card-icon-bg: rgba(34,197,94,0.1); --card-icon-color: var(--ft-success)">
            <div>
                <span class="stat-number"><?= $hiredCount ?></span>
                <p class="stat-label">Offers Accepted</p>
            </div>
            <div class="card-icon"><i class="bi bi-person-plus"></i></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- ── RECENT APPLICANTS FEED ── -->
    <div class="col-lg-8">
        <div class="glass-card p-4">
            <h5 class="section-title mb-4"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Recent Applicants</h5>
            
            <?php if (empty($recentApplicants)): ?>
                <div class="text-center py-5">
                    <div class="text-muted mb-3"><i class="bi bi-people" style="font-size:2.5rem;opacity:0.4;"></i></div>
                    <p class="text-muted mb-0">No one has applied to your listings yet.</p>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($recentApplicants as $applicant): 
                        $initials = strtoupper(substr($applicant['full_name'] ?? 'C', 0, 1) . (strpos($applicant['full_name'] ?? '', ' ') ? substr(explode(' ', $applicant['full_name'])[1], 0, 1) : ''));
                    ?>
                        <div class="p-3 bg-white bg-opacity-5 rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-3 transition-base hover-lift">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px; font-size:1rem;">
                                    <?= $initials ?>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-white fw-bold"><?= e($applicant['full_name']) ?></h6>
                                    <p class="text-muted small mb-0">Applied for <span class="text-white"><?= e($applicant['job_title']) ?></span> &middot; <?= timeAgo($applicant['applied_at']) ?></p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <?= statusBadge($applicant['status']) ?>
                                <a href="<?= url('recruiter/jobs/applicants.php?job_id=' . $applicant['job_id']) ?>" class="btn btn-sm btn-outline-primary hover-lift"><i class="bi bi-arrow-right"></i> Process</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── ACTIVE POSTINGS & METADATA ── -->
    <div class="col-lg-4">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="section-title"><i class="bi bi-card-checklist me-2 text-secondary"></i>Top Postings</h5>
                <a href="<?= url('recruiter/jobs/index.php') ?>" class="text-primary small text-decoration-none">Manage All</a>
            </div>

            <?php if (empty($topJobs)): ?>
                <p class="text-muted small mb-0">No active job listings.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($topJobs as $j): ?>
                        <div class="p-2.5 bg-white bg-opacity-5 rounded-3 border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-white small fw-bold"><a href="<?= url('recruiter/jobs/applicants.php?job_id=' . $j['id']) ?>" class="text-decoration-none text-white"><?= e($j['title']) ?></a></h6>
                                <span class="text-muted" style="font-size:0.75rem;">Active</span>
                            </div>
                            <span class="badge bg-primary bg-opacity-15 text-primary py-2 px-3 border border-primary border-opacity-10 rounded-pill"><?= $j['applicants'] ?> Candidates</span>
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
