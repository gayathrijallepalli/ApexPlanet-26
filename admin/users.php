<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $userId = (int) ($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'block') {
        $db->prepare("UPDATE users SET status = 'blocked' WHERE id = ?")->execute([$userId]);
        logAdminAction($db, (int) $_SESSION['user_id'], 'block_user', 'user', $userId);
        setFlash('success', 'User account blocked.');
    } elseif ($action === 'unblock') {
        $db->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$userId]);
        logAdminAction($db, (int) $_SESSION['user_id'], 'unblock_user', 'user', $userId);
        setFlash('success', 'User account unblocked.');
    } elseif ($action === 'delete') {
        $db->prepare('DELETE FROM users WHERE id = ? AND role_id != 1')->execute([$userId]);
        logAdminAction($db, (int) $_SESSION['user_id'], 'delete_user', 'user', $userId);
        setFlash('success', 'User account deleted.');
    }
    redirect(url('admin/users.php'));
}

$stmt = $db->query(
    "SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug != 'admin' ORDER BY u.created_at DESC"
);
$users = $stmt->fetchAll();

$pageTitle = 'Platform Users';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="mb-4">
    <h1 class="page-heading">Platform Users</h1>
    <p class="page-subtitle">Track registered user details, verify roles, and toggle access blocks.</p>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="glass-card p-4">
            <h5 class="section-title mb-4"><i class="bi bi-people-fill text-primary me-2"></i>User Directory</h5>
            
            <?php if (empty($users)): ?>
                <p class="text-muted mb-0">No users registered on the platform.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($users as $u): 
                        $initials = strtoupper(substr($u['full_name'] ?? 'U', 0, 1) . (strpos($u['full_name'] ?? '', ' ') ? substr(explode(' ', $u['full_name'])[1], 0, 1) : ''));
                        $isBlocked = $u['status'] === 'blocked';
                        $avatarGrad = $u['role_name'] === 'Recruiter' ? 'var(--grad-blue-violet)' : 'var(--grad-blue-cyan)';
                    ?>
                        <div class="p-3 bg-white bg-opacity-5 rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-3 transition-base hover-lift border-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" 
                                     style="width: 44px; height: 44px; font-size:1rem; background: <?= $avatarGrad ?>; flex-shrink: 0;">
                                    <?= $initials ?>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-white fw-bold"><?= e($u['full_name']) ?></h6>
                                    <p class="text-muted small mb-0">
                                        <span><i class="bi bi-envelope me-1"></i><?= e($u['email']) ?></span> &middot; 
                                        <span><i class="bi bi-person-badge me-1"></i><?= e($u['role_name']) ?></span> &middot; 
                                        <span><i class="bi bi-calendar-event me-1"></i>Joined <?= formatDate($u['created_at']) ?></span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center gap-3">
                                <?= statusBadge($u['status']) ?>
                                
                                <form method="POST" onsubmit="return confirm('Execute operation on this user account?')" class="d-flex gap-1.5">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <?php if ($isBlocked): ?>
                                        <button type="submit" name="action" value="unblock" class="btn btn-sm btn-success hover-lift"><i class="bi bi-unlock me-1"></i>Unblock</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="block" class="btn btn-sm btn-warning hover-lift"><i class="bi bi-lock me-1"></i>Block</button>
                                    <?php endif; ?>
                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger hover-lift" onclick="return confirm('Warning: Deleting a user is permanent and cascades into their profiles/applications. Continue?')"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
