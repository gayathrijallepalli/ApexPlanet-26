<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    
    $settings = [
        'smtp_host' => trim($_POST['smtp_host'] ?? ''),
        'smtp_port' => trim($_POST['smtp_port'] ?? '587'),
        'smtp_user' => trim($_POST['smtp_user'] ?? ''),
        'smtp_pass' => $_POST['smtp_pass'] ?? '',
        'smtp_from' => trim($_POST['smtp_from'] ?? ''),
        'smtp_from_name' => trim($_POST['smtp_from_name'] ?? 'SmartHire Pro'),
    ];

    try {
        $stmt = $db->prepare(
            "INSERT INTO app_settings (setting_key, setting_value) 
             VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP"
        );
        
        foreach ($settings as $key => $val) {
            $stmt->execute([$key, $val, $val]);
        }
        
        logAdminAction($db, (int) $_SESSION['user_id'], 'update_smtp_config', 'settings', null);
        setFlash('success', 'SMTP Configuration updated successfully.');
    } catch (Exception $e) {
        setFlash('danger', 'Failed to save configuration: ' . $e->getMessage());
    }
    
    redirect(url('admin/smtp-config.php'));
}

// Fetch current configurations
$currentConfig = [];
try {
    $stmt = $db->query("SELECT setting_key, setting_value FROM app_settings WHERE setting_key LIKE 'smtp_%'");
    $currentConfig = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
} catch (Exception $e) {
    // Table might be missing or unmigrated
}

$pageTitle = 'SMTP Relay Settings';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="mb-4">
    <h1 class="page-heading">Email Gateway Settings</h1>
    <p class="page-subtitle">Configure the SMTP relay host used for OTP verification, schedules, and recruitment notifications.</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-white mb-4"><i class="bi bi-envelope-gear text-primary me-2"></i>Relay Parameters</h5>
            <form method="POST">
                <?= csrfField() ?>
                
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label text-muted small">SMTP Hostname *</label>
                        <input type="text" name="smtp_host" class="form-control" placeholder="e.g. smtp.gmail.com" required value="<?= e($currentConfig['smtp_host'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">SMTP Port *</label>
                        <input type="number" name="smtp_port" class="form-control" placeholder="e.g. 587" required value="<?= e($currentConfig['smtp_port'] ?? '587') ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Authentication Username *</label>
                        <input type="text" name="smtp_user" class="form-control" placeholder="e.g. your-system@gmail.com" required value="<?= e($currentConfig['smtp_user'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Authentication Password *</label>
                        <div class="input-group">
                            <input type="password" name="smtp_pass" id="smtpPass" class="form-control" placeholder="SMTP password or token" value="<?= e($currentConfig['smtp_pass'] ?? '') ?>">
                            <button class="btn btn-outline-primary" type="button" onclick="togglePasswordVisibility()"><i class="bi bi-eye" id="toggleIcon"></i></button>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Sender 'From' Address *</label>
                        <input type="email" name="smtp_from" class="form-control" placeholder="e.g. noreply@company.com" required value="<?= e($currentConfig['smtp_from'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Sender Name *</label>
                        <input type="text" name="smtp_from_name" class="form-control" placeholder="e.g. SmartHire Pro HR" required value="<?= e($currentConfig['smtp_from_name'] ?? 'SmartHire Pro') ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary btn-gradient hover-lift px-4"><i class="bi bi-save me-2"></i>Save Connection Parameters</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="glass-card p-4">
            <h5 class="section-title mb-3"><i class="bi bi-lightbulb-fill text-warning me-2"></i>Relay Guidelines</h5>
            <p class="text-muted small mb-2" style="line-height:1.6;">
                <strong>Port Configuration:</strong><br>
                Use port <strong>465</strong> for SSL configurations, or port <strong>587</strong> for TLS/STARTTLS.
            </p>
            <p class="text-muted small mb-0" style="line-height:1.6;">
                <strong>App Specific Tokens:</strong><br>
                For Gmail relays, configure an <em>App Password</em> in your Google Account security parameters instead of using the raw primary password.
            </p>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const input = document.getElementById('smtpPass');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
