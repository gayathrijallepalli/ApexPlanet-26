<?php
$dashboardLayout = true;
$user = currentUser();
$unreadNotifications = 0;
if ($user) {
    require_once BASE_PATH . '/services/NotificationService.php';
    $unreadNotifications = NotificationService::getUnreadCount($db, $user['id']);
}
$sidebarFile = BASE_PATH . '/includes/sidebar-' . ($user['role_slug'] ?? 'jobseeker') . '.php';
$userInitials = strtoupper(substr($user['full_name'] ?? 'U', 0, 1) . (strpos($user['full_name'] ?? '', ' ') ? substr(explode(' ', $user['full_name'])[1], 0, 1) : ''));
?>
<div class="dashboard-wrapper">
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- ══════════ SIDEBAR ══════════ -->
    <aside class="dashboard-sidebar" id="dashSidebar">
        <!-- Brand -->
        <div class="sidebar-brand">
            <a href="<?= url('index.php') ?>">
                <div class="brand-icon"><i class="bi bi-briefcase-fill"></i></div>
                <span>SmartHire Pro</span>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav" id="sidebarNav">
            <?php if (file_exists($sidebarFile)) include $sidebarFile; ?>
        </nav>

        <!-- User Footer -->
        <div class="sidebar-user">
            <div class="user-avatar"><?= e($userInitials ?: 'U') ?></div>
            <div class="user-info">
                <div class="user-name"><?= e($user['full_name'] ?? 'User') ?></div>
                <div class="user-role"><?= e($user['role_name'] ?? '') ?></div>
            </div>
            <a href="<?= url('auth/logout.php') ?>" title="Logout" style="color:var(--ft-muted);font-size:1rem;flex-shrink:0;text-decoration:none;" onmouseover="this.style.color='var(--ft-danger)';" onmouseout="this.style.color='var(--ft-muted)';">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </aside>

    <!-- ══════════ MAIN CONTENT ══════════ -->
    <div class="dashboard-main">

        <!-- Topbar -->
        <header class="dashboard-topbar">
            <!-- Mobile menu toggle -->
            <button class="topbar-btn d-lg-none me-2" id="sidebarToggle" onclick="openSidebar()" style="border:none;">
                <i class="bi bi-list fs-5"></i>
            </button>

            <!-- Search (hidden on mobile) -->
            <div class="topbar-search d-none d-md-block">
                <i class="bi bi-search search-icon"></i>
                <input type="text" placeholder="Search jobs, companies, skills..." id="topbarSearch">
            </div>

            <!-- Actions -->
            <div class="topbar-actions">
                <!-- Dark Mode -->
                <button class="topbar-btn" id="darkModeToggle" title="Toggle theme" style="border:none;">
                    <i class="bi bi-moon-stars" id="themeIcon"></i>
                </button>

                <!-- Notifications -->
                <div class="dropdown">
                    <button class="topbar-btn position-relative" id="notifToggle" data-bs-toggle="dropdown" aria-expanded="false" style="border:none;">
                        <i class="bi bi-bell"></i>
                        <?php if ($unreadNotifications > 0): ?>
                        <span class="notif-count" id="notifBadge"><?= min($unreadNotifications, 99) ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0" id="notifDropdown">
                        <div style="padding:0.85rem 1rem;border-bottom:1px solid var(--ft-border);display:flex;align-items:center;justify-content:space-between;">
                            <span style="font-weight:700;font-size:0.9rem;color:var(--ft-text);">Notifications</span>
                            <button class="btn btn-link btn-sm p-0" style="font-size:0.75rem;color:var(--ft-primary);" id="markAllReadBtn" onclick="markAllRead()">Mark all read</button>
                        </div>
                        <div id="notifList" style="padding:1rem;color:var(--ft-muted);font-size:0.875rem;text-align:center;">
                            <div class="spinner-border spinner-border-sm"></div>
                        </div>
                        <div style="padding:0.6rem 1rem;border-top:1px solid var(--ft-border);text-align:center;">
                            <a href="#" style="font-size:0.8rem;color:var(--ft-primary);">View all notifications</a>
                        </div>
                    </div>
                </div>

                <!-- User chip -->
                <a href="<?= url(($_SESSION['role_slug'] ?? 'jobseeker') . '/dashboard.php') ?>" class="topbar-user-chip text-decoration-none">
                    <div class="chip-avatar"><?= e($userInitials ?: 'U') ?></div>
                    <span class="chip-name d-none d-md-inline"><?= e(explode(' ', $user['full_name'] ?? 'User')[0]) ?></span>
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <main class="dashboard-content">
            <?= renderFlash() ?>
