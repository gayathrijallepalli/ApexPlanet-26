<?php
require_once __DIR__ . '/includes/init.php';

$stats = [
    'jobs' => 0,
    'companies' => 0,
    'seekers' => 0,
];
try {
    $stats['jobs'] = (int) $db->query(
        "SELECT COUNT(*) FROM jobs j JOIN companies c ON c.id = j.company_id
         WHERE j.status = 'active' AND c.approval_status = 'approved'"
    )->fetchColumn();
    $stats['companies'] = (int) $db->query(
        "SELECT COUNT(*) FROM companies WHERE approval_status = 'approved'"
    )->fetchColumn();
    $stats['seekers'] = (int) $db->query(
        "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = 'jobseeker'"
    )->fetchColumn();
} catch (Throwable $e) {
    // DB not ready
}

$topCompanies = [];
try {
    $stmt = $db->query(
        "SELECT c.name, COUNT(j.id) AS job_count FROM companies c
         JOIN jobs j ON j.company_id = c.id
         WHERE c.approval_status = 'approved' AND j.status = 'active'
         GROUP BY c.id ORDER BY job_count DESC LIMIT 6"
    );
    $topCompanies = $stmt->fetchAll();
} catch (Throwable $e) {
}

$pageTitle = 'Find Your Dream Job';
$bodyClass = 'landing-page';
$extraCss = [asset('css/landing.css')];
$extraJs = [asset('js/job-search.js')];
require_once BASE_PATH . '/includes/header.php';
?>
<section class="hero-section">
    <div class="container text-center">
        <div class="hero-badge">
            <i class="bi bi-stars"></i> India's trusted recruitment platform
        </div>
        <h1 class="hero-title">Find the job that<br><span>fits your future</span></h1>
        <p class="hero-subtitle">Search <?= $stats['jobs'] > 0 ? number_format($stats['jobs']) . '+' : 'thousands of' ?> openings from top companies. Apply in one click and track your application journey.</p>

        <div class="search-panel">
            <form id="jobSearchForm">
                <div class="row g-2 g-md-3">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1 text-start d-block">What</label>
                        <input type="text" name="q" class="form-control" placeholder="Job title, skills, company...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1 text-start d-block">Where</label>
                        <input type="text" name="location" class="form-control" placeholder="City or Remote">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1 text-start d-block">Type</label>
                        <select name="job_type" class="form-select">
                            <option value="">All types</option>
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                            <option value="remote">Remote</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1 text-start d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100 py-2"><i class="bi bi-search me-2"></i>Search Jobs</button>
                    </div>
                </div>
                <input type="hidden" name="experience" id="filterExperience" value="">
                <input type="hidden" name="salary" id="filterSalary" value="">
            </form>
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-2 mt-4" id="quickFilters">
            <button type="button" class="category-chip" data-type="remote"><i class="bi bi-laptop"></i> Remote</button>
            <button type="button" class="category-chip" data-type="full-time"><i class="bi bi-briefcase"></i> Full-time</button>
            <button type="button" class="category-chip" data-type="internship"><i class="bi bi-mortarboard"></i> Internship</button>
            <button type="button" class="category-chip" data-exp="Entry"><i class="bi bi-person"></i> Fresher</button>
            <button type="button" class="category-chip" data-loc="Bangalore"><i class="bi bi-geo-alt"></i> Bangalore</button>
            <button type="button" class="category-chip" data-loc="Mumbai"><i class="bi bi-geo-alt"></i> Mumbai</button>
        </div>
    </div>
</section>

<section class="stats-bar">
    <div class="container">
        <div class="row g-4">
            <div class="col-4 col-md-3">
                <div class="stat-item">
                    <div class="stat-num" id="statJobs"><?= number_format($stats['jobs']) ?></div>
                    <div class="stat-label">Active Jobs</div>
                </div>
            </div>
            <div class="col-4 col-md-3">
                <div class="stat-item">
                    <div class="stat-num"><?= number_format(max($stats['companies'], 5)) ?>+</div>
                    <div class="stat-label">Hiring Companies</div>
                </div>
            </div>
            <div class="col-4 col-md-3">
                <div class="stat-item">
                    <div class="stat-num"><?= number_format(max($stats['seekers'], 100)) ?>+</div>
                    <div class="stat-label">Job Seekers</div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="stat-item">
                    <div class="stat-num">24h</div>
                    <div class="stat-label">Avg. Response Time</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($topCompanies): ?>
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <p class="text-center text-muted small mb-3 text-uppercase fw-semibold" style="letter-spacing:0.08em">Trusted by leading companies</p>
        <div class="company-strip">
            <?php foreach ($topCompanies as $co): ?>
                <div class="company-pill"><?= e($co['name']) ?> <span class="text-primary">(<?= (int) $co['job_count'] ?>)</span></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-5">
    <div class="container">
        <?= renderFlash() ?>

        <?php if ($stats['jobs'] === 0): ?>
        <div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-info-circle fs-4"></i>
            <div>
                <strong>No jobs in database yet.</strong>
                Load demo data to see a realistic homepage:
                <a href="<?= url('seed-demo.php') ?>" class="alert-link">Load Demo Jobs</a>
            </div>
        </div>
        <?php endif; ?>

        <div class="jobs-section-header">
            <div>
                <h2 class="h4 fw-bold mb-1">Latest openings</h2>
                <p class="text-muted small mb-0" id="jobsCountLabel">Showing all available positions</p>
            </div>
            <a href="<?= url('auth/register.php') ?>" class="btn btn-outline-primary btn-sm">Create free account</a>
        </div>

        <div class="row g-3" id="jobResults">
            <div class="col-12 text-center text-muted py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 mb-0">Loading jobs...</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h4 fw-bold">Why SmartHire Pro?</h2>
            <p class="text-muted">Everything you need to land your next role — in one platform</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="shp-card p-4 h-100 text-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3"><i class="bi bi-search"></i></div>
                    <h5>Smart Job Search</h5>
                    <p class="text-muted small mb-0">Filter by location, salary, experience, and job type. Results update instantly as you type.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="shp-card p-4 h-100 text-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-3"><i class="bi bi-graph-up-arrow"></i></div>
                    <h5>Profile Strength Score</h5>
                    <p class="text-muted small mb-0">Get a 100-point resume score and tips to improve your chances of getting shortlisted.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="shp-card p-4 h-100 text-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-3"><i class="bi bi-bell"></i></div>
                    <h5>Application Tracking</h5>
                    <p class="text-muted small mb-0">Track every stage — applied, shortlisted, interview, selected — with real-time notifications.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container text-center">
        <h2 class="h3 fw-bold mb-3">Ready to take the next step?</h2>
        <p class="opacity-75 mb-4">Join thousands of professionals finding their dream jobs on SmartHire Pro.</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?= url('auth/register.php') ?>" class="btn btn-accent btn-lg px-4">Register as Job Seeker</a>
            <a href="<?= url('auth/register.php') ?>" class="btn btn-outline-light btn-lg px-4">Post Jobs as Recruiter</a>
        </div>
    </div>
</section>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
