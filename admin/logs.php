<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['admin']);

$stmt = $db->query(
    'SELECT l.*, u.full_name AS admin_name FROM admin_logs l JOIN users u ON u.id = l.admin_id ORDER BY l.created_at DESC LIMIT 100'
);
$logs = $stmt->fetchAll();

$pageTitle = 'Admin Logs';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>
<h1 class="h3 fw-bold mb-4">Admin Logs</h1>
<div class="shp-card p-4">
    <table class="table table-sm">
        <thead><tr><th>Admin</th><th>Action</th><th>Target</th><th>Details</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= e($log['admin_name']) ?></td>
                <td><?= e($log['action']) ?></td>
                <td><?= e(($log['target_type'] ?? '') . ' #' . ($log['target_id'] ?? '')) ?></td>
                <td><?= e($log['details'] ?? '—') ?></td>
                <td><?= formatDate($log['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
