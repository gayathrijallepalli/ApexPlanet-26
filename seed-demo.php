<?php
require_once __DIR__ . '/includes/init.php';
require_once BASE_PATH . '/includes/demo_seed.php';

$messages = [];
$errors = [];

try {
    $messages = seedDemoData($db);
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seed Demo Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
<div class="container" style="max-width:560px">
    <div class="card shadow">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">SmartHire Pro — Demo Data</h1>
            <?php foreach ($messages as $m): ?><div class="alert alert-success mb-2"><?= htmlspecialchars($m) ?></div><?php endforeach; ?>
            <?php foreach ($errors as $e): ?><div class="alert alert-danger mb-2"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
            <?php if (empty($errors)): ?>
                <a href="index.php" class="btn btn-primary">View Homepage</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
