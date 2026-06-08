<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['admin']);
require_once BASE_PATH . '/services/AnalyticsService.php';

$stats = AnalyticsService::getDashboardStats($db);

$pageTitle = 'Admin Workspace';
$extraJs = ['https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', asset('js/charts-admin.js'), asset('js/dashboard.js')];
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="page-heading">Platform Overview</h1>
        <p class="page-subtitle">Consolidated telemetry, system health indexes, and growth statistics.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/smtp-config.php') ?>" class="btn btn-outline-primary hover-lift"><i class="bi bi-gear me-2"></i>Mail Config</a>
        <a href="<?= url('admin/reports.php') ?>" class="btn btn-primary btn-gradient hover-lift"><i class="bi bi-file-earmark-bar-graph me-2"></i>Analytics Reports</a>
    </div>
</div>

<!-- ── TELEMETRY STATS ROW ── -->
<div class="row g-4 mb-4">
    <?php
    $cards = [
        ['Total Platform Users', $stats['total_users'], 'people', 'var(--grad-blue-cyan)', 'rgba(6,182,212,0.1)', '#06B6D4'],
        ['Verified Recruiters', $stats['total_recruiters'], 'building', 'var(--grad-blue-violet)', 'rgba(139,92,246,0.1)', '#A78BFA'],
        ['Published Job Posts', $stats['total_jobs'], 'briefcase', 'var(--ft-success)', 'rgba(34,197,94,0.1)', '#22C55E'],
        ['Applications Submitted', $stats['total_applications'], 'file-earmark-text', 'var(--ft-warning)', 'rgba(245,158,11,0.1)', '#F59E0B'],
    ];
    foreach ($cards as [$label, $value, $icon, $accent, $iconBg, $iconColor]):
    ?>
    <div class="col-md-6 col-xl-3">
        <div class="ft-stat-card d-flex align-items-center justify-content-between p-4" style="--card-accent: <?= $accent ?>">
            <div>
                <span class="stat-number"><?= number_format($value) ?></span>
                <p class="stat-label"><?= $label ?></p>
            </div>
            <div class="card-icon" style="background: <?= $iconBg ?>; color: <?= $iconColor ?>;"><i class="bi bi-<?= $icon ?>"></i></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <!-- ── CHART MATRIX ── -->
    <div class="col-lg-8">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="glass-card p-4">
                    <h6 class="fw-bold text-white mb-3"><i class="bi bi-graph-up me-2 text-primary"></i>User Growth Index</h6>
                    <div style="position: relative; height: 180px;"><canvas id="userGrowthChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card p-4">
                    <h6 class="fw-bold text-white mb-3"><i class="bi bi-file-earmark-arrow-up me-2 text-secondary"></i>Applications Rate</h6>
                    <div style="position: relative; height: 180px;"><canvas id="appsChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card p-4">
                    <h6 class="fw-bold text-white mb-3"><i class="bi bi-briefcase me-2 text-warning"></i>Job Posts Volume</h6>
                    <div style="position: relative; height: 180px;"><canvas id="jobsChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card p-4">
                    <h6 class="fw-bold text-white mb-3"><i class="bi bi-trophy me-2 text-success"></i>Hiring Companies Share</h6>
                    <div style="position: relative; height: 180px;"><canvas id="topCompaniesChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── SYSTEM HEALTH & ALERTS ── -->
    <div class="col-lg-4">
        <!-- System Health Meter -->
        <div class="glass-card p-4 text-center mb-4">
            <span class="badge bg-success bg-opacity-15 text-success mb-3 px-3 py-1 rounded-pill"><i class="bi bi-heart-pulse-fill me-1"></i>Server Integrity</span>
            <div class="circular-gauge-wrapper mb-3">
                <svg class="progress-ring" width="90" height="90">
                    <defs>
                        <linearGradient id="healthGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#22c55e" />
                            <stop offset="100%" stop-color="#06b6d4" />
                        </linearGradient>
                    </defs>
                    <circle stroke="rgba(255,255,255,0.05)" stroke-width="6" fill="transparent" r="38" cx="45" cy="45"/>
                    <circle class="progress-ring__circle" stroke="url(#healthGrad)" stroke-width="6" fill="transparent" r="38" cx="45" cy="45" stroke-linecap="round" data-percent="98"/>
                </svg>
                <div class="gauge-percentage" style="font-size:1.2rem;">
                    98%
                    <span>Optimal</span>
                </div>
            </div>
            
            <div class="text-start mt-4">
                <div class="d-flex align-items-center justify-content-between mb-2 small border-bottom border-white border-opacity-5 pb-2">
                    <span class="text-muted">Database Engine</span>
                    <span class="d-flex align-items-center gap-1.5 small text-success fw-bold"><i class="bi bi-circle-fill" style="font-size:0.55rem;"></i> Active</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2 small border-bottom border-white border-opacity-5 pb-2">
                    <span class="text-muted">SMTP Relay Gateway</span>
                    <span class="d-flex align-items-center gap-1.5 small text-success fw-bold"><i class="bi bi-circle-fill" style="font-size:0.55rem;"></i> Online</span>
                </div>
                <div class="d-flex align-items-center justify-content-between small">
                    <span class="text-muted">Memory Allocation</span>
                    <span class="text-white fw-bold">128MB / 512MB</span>
                </div>
            </div>
        </div>

        <!-- Pending Approvals Quick-actions -->
        <div class="glass-card p-4">
            <h6 class="fw-bold text-white mb-3"><i class="bi bi-bell-fill text-warning me-2"></i>Admin Review Items</h6>
            <?php
            // Fetch count of pending recruiters
            $stmt = $db->query("SELECT COUNT(*) FROM companies WHERE approval_status = 'pending'");
            $pendingCount = (int) $stmt->fetchColumn();
            ?>
            <div class="p-3 bg-white bg-opacity-5 rounded-3 d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-0 text-white small fw-bold">Recruiter Signups</h6>
                    <span class="text-muted" style="font-size:0.75rem;">Awaiting document verification</span>
                </div>
                <span class="badge bg-warning bg-opacity-15 text-warning py-2 px-3 border border-warning border-opacity-10 rounded-pill"><?= $pendingCount ?> pending</span>
            </div>
            <a href="<?= url('admin/recruiters.php') ?>" class="btn btn-sm btn-outline-primary w-100 hover-lift">Review Pending Registrations</a>
        </div>
    </div>
</div>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
