<?php
require_once __DIR__ . '/../includes/init.php';
require_once BASE_PATH . '/services/AuthService.php';

$userId = $_SESSION['reset_user_id'] ?? null;
if (!$userId) {
    redirect(url('auth/forgot-password.php'));
}

$error = '';
$devOtp = $_SESSION['dev_otp'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $otp = trim($_POST['otp'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (AuthService::verifyOtp($db, $userId, $otp, 'password_reset')) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $userId]);
        unset($_SESSION['reset_user_id'], $_SESSION['dev_otp']);
        setFlash('success', 'Password reset successfully. Please login.');
        redirect(url('auth/login.php'));
    } else {
        $error = 'Invalid or expired OTP.';
    }
}

$pageTitle = 'Reset Password';
$hideNav = true;
require_once BASE_PATH . '/includes/header.php';
?>
<div class="auth-wrapper">
    <div class="auth-card">
        <h1 class="h4 fw-bold mb-3">Reset Password</h1>
        <?php if (defined('DEV_MODE') && DEV_MODE && $devOtp): ?>
        <div class="dev-otp-banner mb-3"><strong>Dev Mode:</strong> OTP: <code><?= e($devOtp) ?></code></div>
        <?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">OTP Code</label>
                <input type="text" name="otp" class="form-control" maxlength="6" required>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" minlength="8" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
