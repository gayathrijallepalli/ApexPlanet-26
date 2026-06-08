<?php
/**
 * One-time installer: imports schema and creates admin user.
 * Run via browser: http://localhost/Task-5/install.php
 * Delete this file after installation in production.
 */
if (!file_exists(__DIR__ . '/config/app.php')) {
    copy(__DIR__ . '/config/app.example.php', __DIR__ . '/config/app.php');
}
require_once __DIR__ . '/config/app.php';

$messages = [];
$errors = [];

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $alreadyInstalled = false;
    $stmt = $pdo->query(
        "SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = " . $pdo->quote(DB_NAME) . " AND table_name = 'roles'"
    );
    if ((int) $stmt->fetchColumn() > 0) {
        $alreadyInstalled = true;
        $messages[] = 'SmartHire Pro is already installed. Database tables exist — skipping schema import.';
    } else {
        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        foreach ($statements as $sql) {
            if ($sql !== '') {
                $pdo->exec($sql);
            }
        }
        $messages[] = 'Database schema imported successfully.';
    }

    $pdo->exec('USE `' . str_replace('`', '``', DB_NAME) . '`');
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['admin@smarthire.pro']);
    if (!$stmt->fetch()) {
        $hash = password_hash('Admin@123', PASSWORD_BCRYPT);
        $pdo->prepare(
            'INSERT INTO users (role_id, email, password, full_name, status, email_verified) VALUES (1, ?, ?, ?, ?, 1)'
        )->execute(['admin@smarthire.pro', $hash, 'System Administrator', 'active']);
        $messages[] = 'Admin user created: admin@smarthire.pro / Admin@123';
    } else {
        $messages[] = 'Admin user already exists.';
    }

    if (!file_exists(__DIR__ . '/config/app.php')) {
        copy(__DIR__ . '/config/app.example.php', __DIR__ . '/config/app.php');
        $messages[] = 'config/app.php created from example.';
    }

    foreach (['uploads/resumes', 'uploads/photos', 'uploads/logos', 'logs'] as $dir) {
        $path = __DIR__ . '/' . $dir;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
            file_put_contents($path . '/.gitkeep', '');
        }
    }
    $messages[] = 'Upload directories ready.';

    require_once __DIR__ . '/includes/demo_seed.php';
    foreach (seedDemoData($pdo) as $seedMsg) {
        $messages[] = $seedMsg;
    }
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartHire Pro Installer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
<div class="container" style="max-width:600px">
    <div class="card shadow">
        <div class="card-body p-4">
            <h1 class="h4 mb-4">SmartHire Pro Installer</h1>
            <?php foreach ($messages as $m): ?>
                <div class="alert alert-success"><?= htmlspecialchars($m) ?></div>
            <?php endforeach; ?>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
            <?php if (empty($errors)): ?>
                <p class="text-muted small">You do not need to run the installer again unless you reset the database.</p>
                <a href="index.php" class="btn btn-primary me-2">Go to SmartHire Pro</a>
                <a href="auth/login.php" class="btn btn-outline-primary">Login</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
