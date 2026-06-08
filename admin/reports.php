<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['admin']);
require_once BASE_PATH . '/services/AnalyticsService.php';

$stats = AnalyticsService::getDashboardStats($db);

$pageTitle = 'Reports';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>
<h1 class="h3 fw-bold mb-4">Platform Reports</h1>
<div class="row g-4">
    <div class="col-md-6">
        <div class="shp-card p-4">
            <h5>Summary</h5>
            <ul class="list-unstyled mb-0">
                <li class="py-2 border-bottom d-flex justify-content-between"><span>Total Users</span><strong><?= $stats['total_users'] ?></strong></li>
                <li class="py-2 border-bottom d-flex justify-content-between"><span>Recruiters</span><strong><?= $stats['total_recruiters'] ?></strong></li>
                <li class="py-2 border-bottom d-flex justify-content-between"><span>Jobs Posted</span><strong><?= $stats['total_jobs'] ?></strong></li>
                <li class="py-2 border-bottom d-flex justify-content-between"><span>Applications</span><strong><?= $stats['total_applications'] ?></strong></li>
                <li class="py-2 d-flex justify-content-between"><span>Active Companies</span><strong><?= $stats['active_companies'] ?></strong></li>
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="shp-card p-4">
            <h5>Application Status Breakdown</h5>
            <?php
            $stmt = $db->query('SELECT status, COUNT(*) AS cnt FROM applications GROUP BY status');
            foreach ($stmt->fetchAll() as $row):
            ?>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span><?= ucwords(str_replace('_', ' ', $row['status'])) ?></span>
                <strong><?= $row['cnt'] ?></strong>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
