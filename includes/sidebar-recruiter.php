<?php
$current = basename($_SERVER['SCRIPT_NAME']);
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
$isActive = fn($file) => ($current === $file || ($currentDir === 'jobs' && str_starts_with($file, 'jobs/'))) ? 'active' : '';
?>
<div class="sidebar-section-label">Overview</div>
<a href="<?= url('recruiter/dashboard.php') ?>" class="<?= $isActive('dashboard.php') ?>">
    <i class="bi bi-grid-1x2"></i> Dashboard
</a>

<div class="sidebar-section-label">Jobs</div>
<a href="<?= url('recruiter/jobs/index.php') ?>" class="<?= ($currentDir === 'jobs') ? 'active' : '' ?>">
    <i class="bi bi-briefcase"></i> Manage Jobs
</a>
<a href="<?= url('recruiter/jobs/create.php') ?>">
    <i class="bi bi-plus-circle"></i> Post New Job
</a>

<div class="sidebar-section-label">Company</div>
<a href="<?= url('recruiter/company.php') ?>" class="<?= $isActive('company.php') ?>">
    <i class="bi bi-building"></i> Company Profile
</a>

<div class="sidebar-section-label">Account</div>
<a href="<?= url('index.php') ?>">
    <i class="bi bi-house"></i> Home Page
</a>
<a href="<?= url('auth/logout.php') ?>">
    <i class="bi bi-box-arrow-right"></i> Logout
</a>
