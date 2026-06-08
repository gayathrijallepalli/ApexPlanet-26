<?php
$pageTitle    = $pageTitle    ?? APP_NAME;
$bodyClass    = $bodyClass    ?? '';
$extraCss     = $extraCss     ?? [];
$extraJs      = $extraJs      ?? [];
$hideNav      = $hideNav      ?? false;
$dashboardLayout = $dashboardLayout ?? (bool) preg_match('#/(admin|recruiter|jobseeker)/#', $_SERVER['SCRIPT_NAME'] ?? '');
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SmartHire Pro — Connecting Talent with Opportunity. Find your dream career or hire top talent faster.">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Core CSS -->
    <link href="<?= asset('css/variables.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/animations.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/main.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/dashboard.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/dark-mode.css') ?>" rel="stylesheet">

    <?php foreach ($extraCss as $css): ?>
    <link href="<?= e($css) ?>" rel="stylesheet">
    <?php endforeach; ?>

    <!-- Apply saved theme BEFORE render to avoid flash -->
    <script>
        (function(){
            var t = localStorage.getItem('shp_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body class="<?= e($bodyClass) ?>">

<!-- Toast Container -->
<div id="toastContainer"></div>

<?php if (!$hideNav && empty($dashboardLayout)): ?>
<!-- ══════════════════════ PUBLIC NAVBAR ══════════════════════ -->
<nav class="navbar navbar-expand-lg ft-navbar sticky-top" id="mainNavbar">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('index.php') ?>">
            <div style="width:34px;height:34px;border-radius:9px;background:var(--grad-blue-cyan);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;">
                <i class="bi bi-briefcase-fill"></i>
            </div>
            <span style="font-family:'Outfit',sans-serif;font-weight:900;font-size:1.25rem;background:var(--grad-blue-cyan);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">SmartHire Pro</span>
        </a>

        <!-- Mobile toggler -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" style="color:var(--ft-muted);">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto ms-3 gap-1">
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('index.php') ?>">
                        <i class="bi bi-search me-1"></i>Browse Jobs
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url($_SESSION['role_slug'] . '/dashboard.php') ?>">
                        <i class="bi bi-grid me-1"></i>Dashboard
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav align-items-lg-center gap-2">
                <!-- Dark Mode Toggle -->
                <li class="nav-item">
                    <button class="topbar-btn" id="darkModeToggle" title="Toggle theme" style="border:none;cursor:pointer;background:rgba(255,255,255,0.06);border:1px solid var(--ft-border);color:var(--ft-muted);">
                        <i class="bi bi-moon-stars" id="themeIcon"></i>
                    </button>
                </li>

                <?php if (isLoggedIn()): ?>
                <li class="nav-item">
                    <a class="btn btn-glass btn-sm px-3" href="<?= url('auth/logout.php') ?>">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('auth/login.php') ?>">Login</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm px-4" href="<?= url('auth/register.php') ?>">
                        Get Started <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>
