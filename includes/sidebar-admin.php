<?php
$current = basename($_SERVER['SCRIPT_NAME']);
$isActive = fn($file) => $current === $file ? 'active' : '';
?>
<div class="sidebar-section-label">Overview</div>
<a href="<?= url('admin/dashboard.php') ?>" class="<?= $isActive('dashboard.php') ?>">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<div class="sidebar-section-label">Management</div>
<a href="<?= url('admin/users.php') ?>" class="<?= $isActive('users.php') ?>">
    <i class="bi bi-people"></i> Users
</a>
<a href="<?= url('admin/recruiters.php') ?>" class="<?= $isActive('recruiters.php') ?>">
    <i class="bi bi-building"></i> Recruiters
    <?php
    try {
        $cnt = (int) $db->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='recruiter' AND u.status='pending' AND u.email_verified=1")->fetchColumn();
        if ($cnt > 0) echo "<span class='nav-badge'>{$cnt}</span>";
    } catch (Throwable $e) {}
    ?>
</a>
<a href="<?= url('admin/jobs.php') ?>" class="<?= $isActive('jobs.php') ?>">
    <i class="bi bi-briefcase"></i> Jobs
</a>

<div class="sidebar-section-label">Analytics</div>
<a href="<?= url('admin/reports.php') ?>" class="<?= $isActive('reports.php') ?>">
    <i class="bi bi-bar-chart-line"></i> Reports
</a>
<a href="<?= url('admin/logs.php') ?>" class="<?= $isActive('logs.php') ?>">
    <i class="bi bi-journal-text"></i> Activity Logs
</a>

<div class="sidebar-section-label">Settings</div>
<a href="<?= url('admin/smtp-config.php') ?>" class="<?= $isActive('smtp-config.php') ?>">
    <i class="bi bi-envelope-gear"></i> SMTP Config
</a>
<a href="<?= url('index.php') ?>">
    <i class="bi bi-house"></i> Home Page
</a>
<a href="<?= url('auth/logout.php') ?>">
    <i class="bi bi-box-arrow-right"></i> Logout
</a>
