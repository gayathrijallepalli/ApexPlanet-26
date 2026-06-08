<?php
require_once __DIR__ . '/../includes/init.php';
require_once BASE_PATH . '/services/AuthService.php';

if (isLoggedIn()) {
    redirectByRole();
}

$error = '';
$devOtp = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'jobseeker';

    if (!$fullName || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['jobseeker', 'recruiter'], true)) {
        $error = 'Invalid role.';
    } else {
        $result = AuthService::register($db, $fullName, $email, $password, $role);
        if ($result['success']) {
            $_SESSION['pending_verify_user_id'] = $result['user_id'];
            if (!empty($result['otp'])) {
                $_SESSION['dev_otp'] = $result['otp'];
            }
            setFlash('success', 'Registration successful! Please verify your email.');
            redirect(url('auth/verify-otp.php'));
        }
        $error = $result['message'];
    }
}

$pageTitle = 'Register';
$hideNav = true;
$bodyClass = 'auth-page';
require_once BASE_PATH . '/includes/header.php';
?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold">Create Account</h1>
            <p class="text-muted">Join SmartHire Pro today</p>
        </div>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" required value="<?= e($_POST['full_name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">I am a</label>
                <select name="role" class="form-select">
                    <option value="jobseeker" <?= ($_POST['role'] ?? '') === 'jobseeker' ? 'selected' : '' ?>>Job Seeker</option>
                    <option value="recruiter" <?= ($_POST['role'] ?? '') === 'recruiter' ? 'selected' : '' ?>>Recruiter</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <p class="text-center mt-3 mb-0 small">Already have an account? <a href="<?= url('auth/login.php') ?>">Login</a></p>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
