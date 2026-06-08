<?php
require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
$location = trim($_GET['location'] ?? '');
$exp = trim($_GET['experience'] ?? '');
$salary = (float) ($_GET['salary'] ?? 0);
$type = trim($_GET['job_type'] ?? '');

$sql = "SELECT j.*, c.name AS company_name, c.logo
        FROM jobs j
        JOIN companies c ON c.id = j.company_id
        WHERE j.status = 'active' AND c.approval_status = 'approved'";
$params = [];

if ($q !== '') {
    $sql .= ' AND (j.title LIKE ? OR j.description LIKE ?)';
    $params[] = "%{$q}%";
    $params[] = "%{$q}%";
}
if ($location !== '') {
    $sql .= ' AND j.location LIKE ?';
    $params[] = "%{$location}%";
}
if ($exp !== '') {
    $sql .= ' AND j.experience_level = ?';
    $params[] = $exp;
}
if ($type !== '') {
    $sql .= ' AND j.job_type = ?';
    $params[] = $type;
}
if ($salary > 0) {
    $sql .= ' AND j.salary_max >= ?';
    $params[] = $salary;
}

$sql .= ' ORDER BY j.created_at DESC LIMIT 24';
$stmt = $db->prepare($sql);
$stmt->execute($params);

echo json_encode(['success' => true, 'jobs' => $stmt->fetchAll()]);
