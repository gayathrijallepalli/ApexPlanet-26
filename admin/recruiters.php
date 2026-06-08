<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $companyId = (int) ($_POST['company_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    // Fetch recruiter details first for email notification
    $recStmt = $db->prepare('SELECT u.email, u.full_name, c.name AS company_name, c.recruiter_id FROM companies c JOIN users u ON u.id = c.recruiter_id WHERE c.id = ?');
    $recStmt->execute([$companyId]);
    $recDetails = $recStmt->fetch();

    if ($recDetails) {
        require_once BASE_PATH . '/services/MailService.php';

        if ($action === 'approve') {
            $db->prepare("UPDATE companies SET approval_status = 'approved' WHERE id = ?")->execute([$companyId]);
            $db->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$recDetails['recruiter_id']]);
            
            logAdminAction($db, (int) $_SESSION['user_id'], 'approve_recruiter', 'company', $companyId);
            
            // Send email notification
            MailService::sendRecruiterApproved($recDetails['email'], $recDetails['full_name'], $recDetails['company_name']);
            setFlash('success', 'Recruiter approved and notified.');
            
        } elseif ($action === 'reject') {
            $db->prepare("UPDATE companies SET approval_status = 'rejected' WHERE id = ?")->execute([$companyId]);
            logAdminAction($db, (int) $_SESSION['user_id'], 'reject_recruiter', 'company', $companyId);
            
            // Send email notification
            MailService::sendRecruiterRejected($recDetails['email'], $recDetails['full_name'], $recDetails['company_name'], 'We could not verify the corporate registry parameters or email domain matching policies for your organization.');
            setFlash('success', 'Recruiter rejected and notified.');
        }
    } else {
        setFlash('danger', 'Recruiter details not found.');
    }
    redirect(url('admin/recruiters.php'));
}

$stmt = $db->query(
    'SELECT c.*, u.full_name, u.email FROM companies c JOIN users u ON u.id = c.recruiter_id ORDER BY c.created_at DESC'
);
$companies = $stmt->fetchAll();

$pageTitle = 'Verify Recruiters';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="mb-4">
    <h1 class="page-heading">Verify Recruiters</h1>
    <p class="page-subtitle">Approve or reject recruiter signups and company credentials.</p>
</div>

<div class="row g-4">
    <!-- PENDING RECRUITERS (COL 8) -->
    <div class="col-lg-8">
        <div class="glass-card p-4">
            <h5 class="section-title mb-4"><i class="bi bi-shield-lock text-warning me-2"></i>Pending Registrations</h5>
            
            <?php 
            $pendingList = array_filter($companies, fn($c) => $c['approval_status'] === 'pending');
            if (empty($pendingList)): ?>
                <div class="text-center py-5">
                    <div class="text-muted mb-3"><i class="bi bi-emoji-smile" style="font-size:2.5rem;opacity:0.4;"></i></div>
                    <p class="text-muted mb-0">Hooray! No pending signups to review.</p>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($pendingList as $c): 
                        $initials = strtoupper(substr($c['name'], 0, 1) . (strpos($c['name'], ' ') ? substr(explode(' ', $c['name'])[1], 0, 1) : ''));
                    ?>
                        <div class="p-3 bg-white bg-opacity-5 rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-3 transition-base hover-lift border-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center fw-bold" style="width: 46px; height: 46px; font-size:1.1rem; background: var(--grad-blue-cyan); color: #fff !important;">
                                    <?= $initials ?>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-white fw-bold"><?= e($c['name']) ?></h6>
                                    <p class="text-muted small mb-0">
                                        <span><i class="bi bi-person me-1"></i><?= e($c['full_name']) ?></span> &middot; 
                                        <span><i class="bi bi-envelope me-1"></i><?= e($c['email']) ?></span>
                                    </p>
                                    <?php if ($c['location']): ?>
                                        <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-geo-alt me-1"></i><?= e($c['location']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <form method="POST" onsubmit="return confirm('Confirm recruiter validation parameters?')" class="d-flex align-items-center gap-2">
                                <?= csrfField() ?>
                                <input type="hidden" name="company_id" value="<?= $c['id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-sm btn-success hover-lift px-3"><i class="bi bi-check2 me-1"></i>Approve</button>
                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger hover-lift px-3"><i class="bi bi-x me-1"></i>Reject</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RECRUITERS LOG (COL 4) -->
    <div class="col-lg-4">
        <div class="glass-card p-4">
            <h5 class="section-title mb-4"><i class="bi bi-folder-check text-success me-2"></i>Recent Verifications</h5>
            
            <?php 
            $processedList = array_filter($companies, fn($c) => $c['approval_status'] !== 'pending');
            if (empty($processedList)): ?>
                <p class="text-muted small mb-0">No recruiters verified yet.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach (array_slice($processedList, 0, 5) as $c): ?>
                        <div class="p-2.5 bg-white bg-opacity-5 rounded-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 text-white small fw-bold text-truncate" style="max-width:180px;"><?= e($c['name']) ?></h6>
                                <span class="text-muted" style="font-size:0.7rem;"><?= e($c['email']) ?></span>
                            </div>
                            <?= statusBadge($c['approval_status']) ?>
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
