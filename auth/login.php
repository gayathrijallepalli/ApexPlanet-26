<?php
require_once __DIR__ . '/../includes/init.php';
require_once BASE_PATH . '/services/AuthService.php';

if (isLoggedIn()) {
    redirectByRole();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = AuthService::login($db, $email, $password);
    if ($result['success']) {
        redirectByRole();
    }
    if (!empty($result['needs_otp'])) {
        $_SESSION['pending_verify_user_id'] = $result['user_id'];
        setFlash('warning', $result['message']);
        redirect(url('auth/verify-otp.php'));
    }
    $error = $result['message'];
}

$pageTitle = 'Login';
$hideNav = true;
require_once BASE_PATH . '/includes/header.php';
?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold">Welcome Back</h1>
            <p class="text-muted">Sign in to SmartHire Pro</p>
        </div>
        <?= renderFlash() ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-4 text-end">
                <a href="<?= url('auth/forgot-password.php') ?>" class="small">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <p class="text-center mt-3 mb-0 small">Don't have an account? <a href="<?= url('auth/register.php') ?>">Register</a></p>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
