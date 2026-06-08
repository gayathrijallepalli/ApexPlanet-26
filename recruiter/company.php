<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['recruiter']);

$userId = (int) $_SESSION['user_id'];
$stmt = $db->prepare('SELECT * FROM companies WHERE recruiter_id = ?');
$stmt->execute([$userId]);
$company = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $logo = $company['logo'] ?? null;

    if (!empty($_FILES['logo']['name'])) {
        $uploaded = uploadFile($_FILES['logo'], 'logos', ALLOWED_IMAGE_TYPES);
        if ($uploaded) $logo = $uploaded;
    }

    if (!$name) {
        setFlash('danger', 'Company name is required.');
    } elseif ($company) {
        $db->prepare(
            'UPDATE companies SET name=?, logo=?, description=?, website=?, location=? WHERE recruiter_id=?'
        )->execute([$name, $logo, $description, $website, $location, $userId]);
        setFlash('success', 'Company profile updated.');
    } else {
        $db->prepare(
            'INSERT INTO companies (recruiter_id, name, logo, description, website, location) VALUES (?,?,?,?,?,?)'
        )->execute([$userId, $name, $logo, $description, $website, $location]);
        setFlash('success', 'Company profile created. Awaiting admin approval.');
    }
    redirect(url('recruiter/company.php'));
}

$pageTitle = 'Company Settings';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="mb-4">
    <h1 class="page-heading">Company Profile</h1>
    <p class="page-subtitle">Manage corporate identities, contact URLs, and business location.</p>
</div>

<div class="row g-4">
    <!-- LEFT SIDEBAR: LOGO & STATUS -->
    <div class="col-lg-4">
        <div class="glass-card p-4 text-center">
            <div class="position-relative d-inline-block mb-3">
                <?php if (!empty($company['logo'])): ?>
                    <img src="<?= url($company['logo']) ?>" alt="Logo" class="rounded-3 border border-2 border-primary border-opacity-30 shadow" style="width: 100px; height: 100px; object-fit: cover;">
                <?php else: ?>
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center border border-2 border-primary border-opacity-10 mx-auto" style="width: 100px; height: 100px; font-size: 2.2rem; font-weight: 800;">
                        <?= strtoupper(substr($company['name'] ?? 'C', 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <h5 class="fw-bold mb-1 text-white"><?= e($company['name'] ?? 'Company Profile') ?></h5>
            <?php if ($company['location']): ?>
                <p class="text-muted small mb-4"><i class="bi bi-geo-alt me-1"></i><?= e($company['location']) ?></p>
            <?php endif; ?>
            
            <?php if ($company): 
                $status = $company['approval_status'];
                $statusAlertClass = $status === 'approved' ? 'alert-success bg-success' : ($status === 'rejected' ? 'alert-danger bg-danger' : 'alert-info bg-info');
                $statusIcon = $status === 'approved' ? 'bi-shield-fill-check' : ($status === 'rejected' ? 'bi-x-circle-fill' : 'bi-hourglass-split');
            ?>
                <div class="alert <?= $statusAlertClass ?> bg-opacity-10 border-0 text-white rounded-3 small p-3 text-start mb-0">
                    <div class="d-flex gap-2 align-items-center mb-1 fw-bold">
                        <i class="bi <?= $statusIcon ?>"></i>
                        <span>Verification Status</span>
                    </div>
                    <p class="text-muted small mb-0">Status: <span class="text-white fw-bold"><?= ucwords($status) ?></span></p>
                    <?php if ($status === 'pending'): ?>
                        <p class="text-muted small mt-1 mb-0" style="font-size:0.75rem;">Administrators will review your credentials soon.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT FORM PANEL -->
    <div class="col-lg-8">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-white mb-4"><i class="bi bi-building me-2 text-primary"></i>Company Information</h5>
            <form method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Company Legal Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Acme Corporation" required value="<?= e($company['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Corporate Website URL</label>
                        <input type="url" name="website" class="form-control" placeholder="https://acme.org" value="<?= e($company['website'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">HQ Location</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g. Pune, Maharashtra" value="<?= e($company['location'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Company Logo Image</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Company Description</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Provide a brief summary of what your company does, its core products, and corporate culture..."><?= e($company['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary btn-gradient hover-lift px-4"><i class="bi bi-save me-2"></i>Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
