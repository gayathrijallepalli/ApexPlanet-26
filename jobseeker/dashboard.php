<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['jobseeker']);

$userId = (int) $_SESSION['user_id'];

// Get user profile details
$stmt = $db->prepare('SELECT * FROM job_seeker_profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: [];

$strength = (int) ($profile['profile_strength'] ?? 0);

// Get application statistics
$stmt = $db->prepare('SELECT COUNT(*) FROM applications WHERE user_id = ?');
$stmt->execute([$userId]);
$appliedCount = (int) $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM saved_jobs WHERE user_id = ?');
$stmt->execute([$userId]);
$savedCount = (int) $stmt->fetchColumn();

// Get recent applications with status
$stmt = $db->prepare(
    'SELECT a.*, j.title, j.job_type, j.location AS job_location, c.name AS company_name, c.logo AS company_logo
     FROM applications a
     JOIN jobs j ON j.id = a.job_id
     JOIN companies c ON c.id = j.company_id
     WHERE a.user_id = ? ORDER BY a.updated_at DESC LIMIT 5'
);
$stmt->execute([$userId]);
$recentApps = $stmt->fetchAll();

// Get upcoming interview details
$stmt = $db->prepare(
    "SELECT a.*, j.title, c.name AS company_name 
     FROM applications a 
     JOIN jobs j ON j.id = a.job_id 
     JOIN companies c ON c.id = j.company_id
     WHERE a.user_id = ? AND a.status = 'interview_scheduled' AND a.interview_date >= NOW()
     ORDER BY a.interview_date ASC LIMIT 1"
);
$stmt->execute([$userId]);
$upcomingInterview = $stmt->fetch();

// Get recommended jobs based on skills (or fallback to latest jobs)
$recommendedJobs = [];
try {
    $skills = array_filter(array_map('trim', explode(',', $profile['skills'] ?? '')));
    if (!empty($skills)) {
        // Construct query to find jobs with matching skills in description or title
        $clauses = [];
        $params = [];
        foreach ($skills as $i => $skill) {
            $clauses[] = "j.title LIKE ? OR j.description LIKE ?";
            $params[] = "%$skill%";
            $params[] = "%$skill%";
        }
        $whereSql = implode(' OR ', $clauses);
        
        $stmt = $db->prepare(
            "SELECT j.*, c.name AS company_name, c.logo AS company_logo
             FROM jobs j
             JOIN companies c ON c.id = j.company_id
             WHERE j.status = 'active' AND c.approval_status = 'approved' AND ({$whereSql})
             ORDER BY j.created_at DESC LIMIT 3"
        );
        $stmt->execute($params);
        $recommendedJobs = $stmt->fetchAll();
    }
} catch (Throwable $e) {
    // Ignore and fallback
}

// Fallback to recent jobs if not enough matches
if (count($recommendedJobs) < 3) {
    $needed = 3 - count($recommendedJobs);
    $excludeIds = array_column($recommendedJobs, 'id') ?: [0];
    $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
    
    $stmt = $db->prepare(
        "SELECT j.*, c.name AS company_name, c.logo AS company_logo
         FROM jobs j
         JOIN companies c ON c.id = j.company_id
         WHERE j.status = 'active' AND c.approval_status = 'approved' AND j.id NOT IN ({$placeholders})
         ORDER BY j.created_at DESC LIMIT {$needed}"
    );
    $stmt->execute(array_merge($excludeIds));
    $recommendedJobs = array_merge($recommendedJobs, $stmt->fetchAll());
}

// Map pipeline steps for easy iteration
$stages = [
    'applied' => 'Applied',
    'under_review' => 'Under Review',
    'shortlisted' => 'Shortlisted',
    'interview_scheduled' => 'Interviewing',
    'selected' => 'Offer Extended',
];

$pageTitle = 'Jobseeker Dashboard';
$extraJs = [asset('js/dashboard.js')];
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="page-heading">Developer Dashboard</h1>
        <p class="page-subtitle">Welcome back, <?= e(explode(' ', $_SESSION['full_name'] ?? 'Candidate')[0]) ?>. Track your applications and explore fits.</p>
    </div>
    <a href="<?= url('jobseeker/jobs.php') ?>" class="btn btn-primary btn-gradient hover-lift"><i class="bi bi-search me-2"></i>Explore Opportunities</a>
</div>

<!-- ── STATS ROW ── -->
<div class="row g-4 mb-4">
    <!-- Profile Strength Card (circular gauge) -->
    <div class="col-md-6 col-xl-3">
        <div class="ft-stat-card d-flex align-items-center justify-content-between p-4" style="--card-accent: var(--grad-blue-cyan)">
            <div>
                <p class="text-muted small mb-2">Profile Integrity</p>
                <a href="<?= url('jobseeker/profile.php') ?>" class="btn btn-sm btn-outline-primary py-1 px-3 border-0 bg-white bg-opacity-5 hover-lift">Complete Profile</a>
            </div>
            <div class="circular-gauge-wrapper">
                <svg class="progress-ring" width="80" height="80">
                    <defs>
                        <linearGradient id="blueCyan" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#2563EB" />
                            <stop offset="100%" stop-color="#06B6D4" />
                        </linearGradient>
                    </defs>
                    <circle stroke="rgba(255,255,255,0.05)" stroke-width="6" fill="transparent" r="32" cx="40" cy="40"/>
                    <circle class="progress-ring__circle" stroke="url(#blueCyan)" stroke-width="6" fill="transparent" r="32" cx="40" cy="40" stroke-linecap="round" data-percent="<?= $strength ?>"/>
                </svg>
                <div class="gauge-percentage" style="font-size: 1.1rem;"><?= $strength ?>%</div>
            </div>
        </div>
    </div>

    <!-- AI Compatibility Score (circular gauge) -->
    <div class="col-md-6 col-xl-3">
        <div class="ft-stat-card d-flex align-items-center justify-content-between p-4" style="--card-accent: var(--grad-blue-violet)">
            <div>
                <p class="text-muted small mb-1">AI Match Compatibility</p>
                <h4 class="mb-0" style="font-size:1.15rem;font-weight:700;color:var(--ft-text);">Strong Matching</h4>
                <p class="text-success small mb-0 mt-1"><i class="bi bi-shield-check me-1"></i>Optimized</p>
            </div>
            <div class="circular-gauge-wrapper">
                <svg class="progress-ring" width="80" height="80">
                    <defs>
                        <linearGradient id="blueViolet" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#3B82F6" />
                            <stop offset="100%" stop-color="#8B5CF6" />
                        </linearGradient>
                    </defs>
                    <circle stroke="rgba(255,255,255,0.05)" stroke-width="6" fill="transparent" r="32" cx="40" cy="40"/>
                    <circle class="progress-ring__circle" stroke="url(#blueViolet)" stroke-width="6" fill="transparent" r="32" cx="40" cy="40" stroke-linecap="round" data-percent="85"/>
                </svg>
                <div class="gauge-percentage" style="font-size: 1.1rem;">85%</div>
            </div>
        </div>
    </div>

    <!-- Applied Jobs Card -->
    <div class="col-md-6 col-xl-3">
        <div class="ft-stat-card d-flex align-items-center justify-content-between p-4" style="--card-accent: var(--ft-success); --card-icon-bg: rgba(34,197,94,0.1); --card-icon-color: var(--ft-success)">
            <div>
                <span class="stat-number"><?= $appliedCount ?></span>
                <p class="stat-label">Applications Submitted</p>
            </div>
            <div class="card-icon"><i class="bi bi-file-earmark-check"></i></div>
        </div>
    </div>

    <!-- Saved Jobs Card -->
    <div class="col-md-6 col-xl-3">
        <div class="ft-stat-card d-flex align-items-center justify-content-between p-4" style="--card-accent: var(--ft-warning); --card-icon-bg: rgba(245,158,11,0.1); --card-icon-color: var(--ft-warning)">
            <div>
                <span class="stat-number"><?= $savedCount ?></span>
                <p class="stat-label">Saved Listings</p>
            </div>
            <div class="card-icon"><i class="bi bi-bookmark-star"></i></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- ── PIPELINE & APPLICATIONS ── -->
    <div class="col-lg-8">
        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="section-title"><i class="bi bi-grid-3x3-gap me-2 text-primary"></i>Candidacy Pipelines</h5>
                <a href="<?= url('jobseeker/applications.php') ?>" class="btn btn-sm btn-link text-decoration-none" style="font-size:0.85rem;color:var(--ft-primary)">View All Applications</a>
            </div>

            <?php if (empty($recentApps)): ?>
                <div class="text-center py-5">
                    <div class="text-muted mb-3"><i class="bi bi-archive" style="font-size:2.5rem;opacity:0.4;"></i></div>
                    <p class="text-muted mb-3">You don't have any active application pipelines.</p>
                    <a href="<?= url('jobseeker/jobs.php') ?>" class="btn btn-outline-primary btn-sm">Find Jobs</a>
                </div>
            <?php else: ?>
                <?php foreach ($recentApps as $app): 
                    $currStatus = $app['status'];
                    $isRejected = $currStatus === 'rejected';
                ?>
                    <div class="p-3 mb-3 glass-card bg-white bg-opacity-5 rounded-3 border-0 transition-base hover-lift">
                        <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                            <div>
                                <h6 class="mb-0 fw-bold"><a href="<?= url('jobseeker/job-detail.php?id=' . $app['job_id']) ?>" class="text-decoration-none text-white"><?= e($app['title']) ?></a></h6>
                                <p class="text-muted small mb-0"><?= e($app['company_name']) ?> &middot; <?= e($app['job_location']) ?></p>
                            </div>
                            <div>
                                <?= statusBadge($currStatus) ?>
                            </div>
                        </div>

                        <?php if ($isRejected): ?>
                            <div class="alert alert-danger py-2 px-3 small border-0 bg-danger bg-opacity-10 text-danger rounded-3 mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>This application is closed. Feel free to apply to other relevant matches.
                            </div>
                        <?php else: ?>
                            <!-- Step Tracker Progress Bar -->
                            <div class="position-relative py-3 mt-2">
                                <div class="progress" style="height: 4px; background: rgba(255,255,255,0.06); border-radius: 99px;">
                                    <?php 
                                        $stagesKeys = array_keys($stages);
                                        $currIndex = array_search($currStatus, $stagesKeys, true);
                                        $percent = ($currIndex !== false) ? ($currIndex / (count($stages) - 1)) * 100 : 0;
                                    ?>
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percent ?>%; transition: width 1.2s ease;"></div>
                                </div>
                                <div class="d-flex justify-content-between position-absolute w-100 top-50 translate-middle-y">
                                    <?php 
                                    $stepIdx = 0;
                                    foreach ($stages as $key => $label): 
                                        $isDone = ($currIndex !== false && $stepIdx <= $currIndex);
                                        $isActive = ($currStatus === $key);
                                    ?>
                                        <div class="d-flex flex-column align-items-center" style="width: 20px;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 14px; height: 14px; background: <?= $isActive ? 'var(--ft-primary)' : ($isDone ? 'var(--ft-secondary)' : '#1E293B') ?>; 
                                                        border: 2px solid <?= $isActive || $isDone ? 'transparent' : 'rgba(255,255,255,0.1)' ?>; 
                                                        box-shadow: <?= $isActive ? '0 0 10px var(--ft-primary)' : 'none' ?>;
                                                        transition: all 0.3s;">
                                            </div>
                                            <span class="d-none d-md-block text-muted mt-2" style="font-size: 0.65rem; white-space: nowrap; font-weight: <?= $isActive ? '700' : '500' ?>; color: <?= $isActive ? 'var(--ft-text) !important' : ($isDone ? '#f8fafc' : '') ?>;">
                                                <?= $label ?>
                                            </span>
                                        </div>
                                    <?php 
                                    $stepIdx++;
                                    endforeach; ?>
                                </div>
                            </div>
                            <div style="height: 12px;" class="d-block d-md-none"></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ── RECOMMENDED JOBS ── -->
        <div class="glass-card p-4">
            <h5 class="section-title mb-4"><i class="bi bi-sparkles me-2 text-warning"></i>Smart Recommendations</h5>
            <div class="row g-3">
                <?php foreach ($recommendedJobs as $job): ?>
                    <div class="col-md-12">
                        <div class="p-3 glass-card bg-white bg-opacity-5 rounded-3 border-0 transition-base hover-lift d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center text-white fw-bold" 
                                     style="width: 44px; height: 44px; background: var(--grad-blue-cyan); font-size: 1.1rem; flex-shrink: 0;">
                                    <?= strtoupper(substr($job['company_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold"><a href="<?= url('jobseeker/job-detail.php?id=' . $job['id']) ?>" class="text-decoration-none text-white"><?= e($job['title']) ?></a></h6>
                                    <p class="text-muted small mb-0"><?= e($job['company_name']) ?> &middot; <?= e($job['location']) ?> &middot; <span class="text-primary"><?= ucfirst($job['job_type']) ?></span></p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3 border border-primary border-opacity-10 rounded-pill" style="font-size:0.75rem;"><i class="bi bi-stars me-1 text-warning"></i>92% Match</span>
                                <a href="<?= url('jobseeker/job-detail.php?id=' . $job['id']) ?>" class="btn btn-sm btn-outline-primary hover-lift"><i class="bi bi-eye"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── SIDEBAR WIDGETS ── -->
    <div class="col-lg-4">
        <!-- Upcoming Interview Widget -->
        <?php if ($upcomingInterview): ?>
            <div class="glass-card p-4 mb-4 border-primary border-opacity-20" style="background: linear-gradient(135deg, rgba(15,23,42,0.95), rgba(37,99,235,0.05))">
                <span class="badge bg-primary mb-3"><i class="bi bi-calendar-event me-1"></i>Upcoming Interview</span>
                <h5 class="fw-bold mb-1 text-white"><?= e($upcomingInterview['title']) ?></h5>
                <p class="text-muted small mb-3"><?= e($upcomingInterview['company_name']) ?></p>
                
                <div class="p-3 bg-white bg-opacity-5 rounded-3 mb-3 border border-white border-opacity-5">
                    <div class="d-flex align-items-center gap-2 mb-2 small text-white">
                        <i class="bi bi-clock text-primary"></i>
                        <span><?= date('M d, Y - H:i A', strtotime($upcomingInterview['interview_date'])) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <i class="bi bi-camera-video text-primary"></i>
                        <span><?= str_replace('_', ' ', ucfirst($upcomingInterview['interview_type'])) ?></span>
                    </div>
                </div>

                <?php if ($upcomingInterview['interview_link']): ?>
                    <a href="<?= e($upcomingInterview['interview_link']) ?>" target="_blank" class="btn btn-primary btn-gradient w-100 hover-lift"><i class="bi bi-camera-video me-2"></i>Join Video Call</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Skill Gap Analysis -->
        <div class="glass-card p-4 mb-4">
            <h5 class="section-title mb-3"><i class="bi bi-diagram-3 me-2 text-secondary"></i>Skill Gap Analysis</h5>
            <p class="text-muted small mb-3">Based on your target matches, enhancing these skills will increase compatibility score:</p>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-white bg-opacity-5 text-white py-2 px-3 rounded-pill border border-white border-opacity-5 hover-glow"><i class="bi bi-plus text-success me-1"></i>Docker</span>
                <span class="badge bg-white bg-opacity-5 text-white py-2 px-3 rounded-pill border border-white border-opacity-5 hover-glow"><i class="bi bi-plus text-success me-1"></i>Kubernetes</span>
                <span class="badge bg-white bg-opacity-5 text-white py-2 px-3 rounded-pill border border-white border-opacity-5 hover-glow"><i class="bi bi-plus text-success me-1"></i>Redis</span>
                <span class="badge bg-white bg-opacity-5 text-white py-2 px-3 rounded-pill border border-white border-opacity-5 hover-glow"><i class="bi bi-plus text-success me-1"></i>AWS (S3/EC2)</span>
                <span class="badge bg-white bg-opacity-5 text-white py-2 px-3 rounded-pill border border-white border-opacity-5 hover-glow"><i class="bi bi-plus text-success me-1"></i>TypeScript</span>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="glass-card p-4">
            <h5 class="section-title mb-3"><i class="bi bi-activity me-2 text-info"></i>Activity Logs</h5>
            <div class="activity-timeline">
                <?php 
                $stmt = $db->prepare('SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 4');
                $stmt->execute([$userId]);
                $activities = $stmt->fetchAll();
                if (empty($activities)): ?>
                    <p class="text-muted small mb-0">No recent activity.</p>
                <?php else: 
                    foreach ($activities as $act):
                        $iconClass = 'bi-info-circle text-muted';
                        if (str_contains($act['action'], 'apply')) $iconClass = 'bi-file-earmark-arrow-up text-primary';
                        if (str_contains($act['action'], 'login')) $iconClass = 'bi-box-arrow-in-right text-success';
                        if (str_contains($act['action'], 'save')) $iconClass = 'bi-bookmark-fill text-warning';
                ?>
                        <div class="d-flex gap-3 mb-3 border-bottom border-white border-opacity-5 pb-3">
                            <div class="rounded-circle bg-white bg-opacity-5 d-flex align-items-center justify-content-center" style="width:30px;height:30px;flex-shrink:0;">
                                <i class="bi <?= $iconClass ?>" style="font-size:0.85rem;"></i>
                            </div>
                            <div>
                                <p class="mb-0 text-white small" style="line-height:1.35;"><?= e($act['action'] === 'apply_job' ? 'Applied for Job' : ($act['action'] === 'login' ? 'Logged In Successfully' : $act['action'])) ?></p>
                                <span class="text-muted" style="font-size:0.75rem;"><?= timeAgo($act['created_at']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
