<?php

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('warning', 'Please log in to continue.');
        redirect(url('auth/login.php'));
    }
}

function requireRole(array $allowedRoles): void
{
    requireLogin();
    $role = $_SESSION['role_slug'] ?? '';
    if (!in_array($role, $allowedRoles, true)) {
        http_response_code(403);
        setFlash('danger', 'Access denied.');
        redirect(url('index.php'));
    }
}

function redirectByRole(): void
{
    $map = [
        'admin' => url('admin/dashboard.php'),
        'recruiter' => url('recruiter/dashboard.php'),
        'jobseeker' => url('jobseeker/dashboard.php'),
    ];
    $role = $_SESSION['role_slug'] ?? '';
    redirect($map[$role] ?? url('index.php'));
}

function loadUserSession(PDO $db, int $userId): void
{
    $stmt = $db->prepare(
        'SELECT u.*, r.slug AS role_slug, r.name AS role_name
         FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?'
    );
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        return;
    }
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role_slug'] = $user['role_slug'];
    $_SESSION['role_name'] = $user['role_name'];
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'email' => $user['email'],
        'full_name' => $user['full_name'],
        'role_slug' => $user['role_slug'],
        'role_name' => $user['role_name'],
        'status' => $user['status'],
    ];
}
