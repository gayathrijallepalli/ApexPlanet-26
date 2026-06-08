<?php
require_once __DIR__ . '/includes/init.php';

$jobId = (int) ($_GET['id'] ?? 0);
$stmt = $db->prepare(
    'SELECT j.*, c.name AS company_name, c.logo, c.website, c.description AS company_desc, c.location AS company_location
     FROM jobs j JOIN companies c ON c.id = j.company_id
     WHERE j.id = ? AND j.status = ? AND c.approval_status = ?'
);
$stmt->execute([$jobId, 'active', 'approved']);
$job = $stmt->fetch();

if (!$job) {
    setFlash('danger', 'Job not found or no longer available.');
    redirect(url('index.php'));
}

$applyUrl = isLoggedIn() && ($_SESSION['role_slug'] ?? '') === 'jobseeker'
    ? url('jobseeker/job-detail.php?id=' . $jobId)
    : url('auth/register.php');

$pageTitle = $job['title'];
$bodyClass = 'landing-page';
$extraCss = [asset('css/landing.css')];
require_once BASE_PATH . '/includes/header.php';
?>
<section class="job-detail-hero py-5">
    <div class="container">
        <a href="<?= url('index.php') ?>" class="text-white-50 small text-decoration-none mb-3 d-inline-block"><i class="bi bi-arrow-left"></i> Back to jobs</a>
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-light text-primary mb-3"><?= e(ucfirst(str_replace('-', ' ', $job['job_type']))) ?></span>
                <h1 class="display-6 fw-bold text-white mb-2"><?= e($job['title']) ?></h1>
                <p class="text-white-50 mb-0 fs-5"><?= e($job['company_name']) ?></p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?= $applyUrl ?>" class="btn btn-accent btn-lg px-4">Apply Now</a>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="shp-card p-4 mb-4">
                    <h5 class="fw-semibold mb-3">About the role</h5>
                    <p class="mb-0"><?= nl2br(e($job['description'])) ?></p>
                </div>
                <?php if ($job['requirements']): ?>
                <div class="shp-card p-4">
                    <h5 class="fw-semibold mb-3">Requirements</h5>
                    <p class="mb-0"><?= nl2br(e($job['requirements'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-4">
                <div class="shp-card p-4 mb-4">
                    <h6 class="fw-semibold mb-3">Job overview</h6>
                    <ul class="list-unstyled job-meta-list mb-0">
                        <li><i class="bi bi-geo-alt"></i><span><?= e($job['location']) ?></span></li>
                        <li><i class="bi bi-briefcase"></i><span><?= e(ucfirst(str_replace('-', ' ', $job['job_type']))) ?></span></li>
                        <li><i class="bi bi-bar-chart"></i><span><?= e($job['experience_level'] ?: 'Any level') ?></span></li>
                        <li><i class="bi bi-currency-rupee"></i><span><?= formatSalary((float) $job['salary_min'], (float) $job['salary_max']) ?></span></li>
                        <li><i class="bi bi-calendar3"></i><span>Posted <?= formatDate($job['created_at']) ?></span></li>
                    </ul>
                </div>
                <div class="shp-card p-4">
                    <h6 class="fw-semibold mb-2"><?= e($job['company_name']) ?></h6>
                    <p class="small text-muted"><?= e($job['company_desc'] ?? '') ?></p>
                    <?php if ($job['website']): ?>
                        <a href="<?= e($job['website']) ?>" target="_blank" class="small">Visit company website</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
