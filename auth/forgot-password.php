<?php
require_once __DIR__ . '/../includes/init.php';
require_once BASE_PATH . '/services/AuthService.php';
require_once BASE_PATH . '/services/MailService.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $email = trim($_POST['email'] ?? '');
    $stmt = $db->prepare('SELECT id, full_name FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) {
        $otp = AuthService::generateOtp($db, (int) $user['id'], 'password_reset');
        MailService::sendOtp($email, $user['full_name'], $otp, 'password_reset');
        $_SESSION['reset_user_id'] = $user['id'];
        if (defined('DEV_MODE') && DEV_MODE) {
            $_SESSION['dev_otp'] = $otp;
        }
        setFlash('success', 'OTP sent to your email.');
        redirect(url('auth/reset-password.php'));
    }
    $success = 'If that email exists, an OTP has been sent.';
}

$pageTitle = 'Forgot Password';
$hideNav = true;
require_once BASE_PATH . '/includes/header.php';
?>
<div class="auth-wrapper">
    <div class="auth-card">
        <h1 class="h4 fw-bold mb-3">Forgot Password</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-info"><?= e($success) ?></div><?php endif; ?>
        <form method="POST">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send OTP</button>
        </form>
        <p class="text-center mt-3 mb-0 small"><a href="<?= url('auth/login.php') ?>">Back to login</a></p>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
