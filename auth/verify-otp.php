<?php
require_once __DIR__ . '/../includes/init.php';
require_once BASE_PATH . '/services/AuthService.php';
require_once BASE_PATH . '/services/MailService.php';

$userId = (int) ($_SESSION['pending_verify_user_id'] ?? 0);
if (!$userId) {
    setFlash('warning', 'No pending verification.');
    redirect(url('auth/login.php'));
}

$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) {
    redirect(url('auth/login.php'));
}

$error = '';

// Ensure a valid OTP exists (fixes expired/missing OTP after page refresh or re-login)
$devOtp = AuthService::getActiveOtp($db, $userId, 'email_verify');
if (!$devOtp) {
    $devOtp = AuthService::generateOtp($db, $userId, 'email_verify');
    MailService::sendOtp($user['email'], $user['full_name'], $devOtp, 'email_verify');
}
if (defined('DEV_MODE') && DEV_MODE) {
    $_SESSION['dev_otp'] = $devOtp;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    if (isset($_POST['resend'])) {
        $devOtp = AuthService::generateOtp($db, $userId, 'email_verify');
        MailService::sendOtp($user['email'], $user['full_name'], $devOtp, 'email_verify');
        $_SESSION['dev_otp'] = $devOtp;
        setFlash('success', 'A new OTP has been sent.');
        redirect(url('auth/verify-otp.php'));
    }

    $otp = trim($_POST['otp'] ?? '');
    if (AuthService::verifyOtp($db, $userId, $otp, 'email_verify')) {
        $stmt = $db->prepare('SELECT slug FROM roles WHERE id = ?');
        $stmt->execute([$user['role_id']]);
        $role = $stmt->fetch();
        $status = ($role['slug'] ?? '') === 'jobseeker' ? 'active' : 'pending';

        $db->prepare('UPDATE users SET email_verified = 1, status = ? WHERE id = ?')->execute([$status, $userId]);
        unset($_SESSION['pending_verify_user_id'], $_SESSION['dev_otp']);

        if ($status === 'active') {
            session_regenerate_id(true);
            loadUserSession($db, $userId);
            setFlash('success', 'Email verified! Welcome to SmartHire Pro.');
            redirectByRole();
        }
        setFlash('success', 'Email verified! Your recruiter account is pending admin approval.');
        redirect(url('auth/login.php'));
    }
    $error = 'Invalid or expired OTP.';
}

$pageTitle = 'Verify Email';
$hideNav = true;
require_once BASE_PATH . '/includes/header.php';
?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold">Verify Email</h1>
            <p class="text-muted">Enter the 6-digit code sent to <?= e($user['email']) ?></p>
        </div>
        <?php if (defined('DEV_MODE') && DEV_MODE && $devOtp): ?>
        <div class="dev-otp-banner mb-3"><strong>Dev Mode:</strong> Your OTP is <code><?= e($devOtp) ?></code> (also logged to logs/otp.log)</div>
        <?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
            <?= csrfField() ?>
            <div class="mb-4">
                <label class="form-label">OTP Code</label>
                <input type="text" name="otp" class="form-control text-center fs-4" maxlength="6" pattern="[0-9]{6}" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100">Verify</button>
        </form>
        <form method="POST" class="mt-3 text-center">
            <?= csrfField() ?>
            <button type="submit" name="resend" value="1" class="btn btn-link btn-sm">Resend OTP</button>
        </form>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
