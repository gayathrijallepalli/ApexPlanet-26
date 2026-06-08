<?php

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function renderFlash(): string
{
    $flash = getFlash();
    if (!$flash) {
        return '';
    }
    $type = e($flash['type']);
    $message = e($flash['message']);
    return "<div class=\"alert alert-{$type} alert-dismissible fade show\" role=\"alert\">{$message}<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>";
}

function logActivity(PDO $db, int $userId, string $action, ?string $details = null): void
{
    $stmt = $db->prepare(
        'INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $action, $details, $_SERVER['REMOTE_ADDR'] ?? null]);
}

function logAdminAction(PDO $db, int $adminId, string $action, ?string $targetType = null, ?int $targetId = null, ?string $details = null): void
{
    $stmt = $db->prepare(
        'INSERT INTO admin_logs (admin_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$adminId, $action, $targetType, $targetId, $details]);
}

function uploadFile(array $file, string $directory, array $allowedTypes, int $maxSize = UPLOAD_MAX_SIZE): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > $maxSize) {
        return null;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedTypes, true)) {
        return null;
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('', true) . '.' . strtolower($ext);
    $destDir = BASE_PATH . '/uploads/' . trim($directory, '/');
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $destPath = $destDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return null;
    }
    return 'uploads/' . trim($directory, '/') . '/' . $filename;
}

function formatDate(?string $date): string
{
    if (!$date) {
        return '—';
    }
    return date('M j, Y', strtotime($date));
}

function formatSalary(?float $min, ?float $max): string
{
    if (!$min && !$max) {
        return 'Salary not disclosed';
    }
    $fmt = fn($n) => '₹' . number_format($n, 0, '.', ',');
    if ($min && $max) {
        return $fmt($min) . ' – ' . $fmt($max);
    }
    return $fmt($min ?: $max);
}

function statusBadge(string $status): string
{
    $map = [
        'applied' => 'secondary',
        'under_review' => 'info',
        'shortlisted' => 'primary',
        'interview_scheduled' => 'warning',
        'selected' => 'success',
        'rejected' => 'danger',
        'active' => 'success',
        'blocked' => 'danger',
        'pending' => 'warning',
        'approved' => 'success',
        'draft' => 'secondary',
        'closed' => 'dark',
    ];
    $class = $map[$status] ?? 'secondary';
    $label = ucwords(str_replace('_', ' ', $status));
    return "<span class=\"badge bg-{$class}\">{$label}</span>";
}

function timeAgo(string $datetime): string
{
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $time);
}
