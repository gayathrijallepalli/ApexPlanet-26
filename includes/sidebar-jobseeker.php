<?php
$current = basename($_SERVER['SCRIPT_NAME']);
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
$isActive = fn($file) => ($current === $file || ($currentDir === 'jobs' && str_starts_with($file, 'jobs/'))) ? 'active' : '';
?>
<div class="sidebar-section-label">Main</div>
<a href="<?= url('jobseeker/dashboard.php') ?>" class="<?= $isActive('dashboard.php') ?>">
    <i class="bi bi-grid-1x2"></i> Dashboard
</a>

<div class="sidebar-section-label">Jobs</div>
<a href="<?= url('jobseeker/jobs.php') ?>" class="<?= $isActive('jobs.php') ?>">
    <i class="bi bi-search"></i> Browse Jobs
</a>
<a href="<?= url('jobseeker/saved-jobs.php') ?>" class="<?= $isActive('saved-jobs.php') ?>">
    <i class="bi bi-bookmark"></i> Saved Jobs
</a>
<a href="<?= url('jobseeker/applications.php') ?>" class="<?= $isActive('applications.php') ?>">
    <i class="bi bi-file-earmark-text"></i> My Applications
    <?php
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ? AND status IN ('shortlisted','interview_scheduled')");
        $stmt->execute([$_SESSION['user_id'] ?? 0]);
        $activeCount = (int) $stmt->fetchColumn();
        if ($activeCount > 0) echo "<span class='nav-badge'>{$activeCount}</span>";
    } catch (Throwable $e) {}
    ?>
</a>

<div class="sidebar-section-label">Profile</div>
<a href="<?= url('jobseeker/profile.php') ?>" class="<?= $isActive('profile.php') ?>">
    <i class="bi bi-person-circle"></i> My Profile
</a>

<div class="sidebar-section-label">Account</div>
<a href="<?= url('index.php') ?>">
    <i class="bi bi-house"></i> Home Page
</a>
<a href="<?= url('auth/logout.php') ?>">
    <i class="bi bi-box-arrow-right"></i> Logout
</a>
