<?php
require_once __DIR__ . '/../includes/init.php';
require_once BASE_PATH . '/services/MailService.php';
require_once BASE_PATH . '/services/NotificationService.php';

header('Content-Type: application/json');

requireLogin();
requireRole(['recruiter']);
requireCsrf();

$applicationId = (int) ($_POST['application_id'] ?? 0);
$interviewDate = trim($_POST['interview_date'] ?? '');
$interviewType = trim($_POST['interview_type'] ?? 'google_meet');
$interviewLink = trim($_POST['interview_link'] ?? '');
$interviewInstructions = trim($_POST['interview_instructions'] ?? '');

if (!$applicationId || !$interviewDate) {
    echo json_encode(['success' => false, 'message' => 'Application ID and Date/Time are required.']);
    exit;
}

// Verify that the applicant's job belongs to this recruiter's company
$stmt = $db->prepare(
    'SELECT a.*, j.title, c.name AS company_name, c.recruiter_id, u.email AS candidate_email, u.full_name AS candidate_name
     FROM applications a
     JOIN jobs j ON j.id = a.job_id
     JOIN companies c ON c.id = j.company_id
     JOIN users u ON u.id = a.user_id
     WHERE a.id = ?'
);
$stmt->execute([$applicationId]);
$app = $stmt->fetch();

if (!$app) {
    echo json_encode(['success' => false, 'message' => 'Application not found.']);
    exit;
}

if ((int) $app['recruiter_id'] !== (int) $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Update application state and interview details
$stmt = $db->prepare(
    'UPDATE applications SET 
        status = ?, 
        interview_date = ?, 
        interview_type = ?, 
        interview_link = ?, 
        interview_instructions = ? 
     WHERE id = ?'
);

try {
    $stmt->execute([
        'interview_scheduled',
        $interviewDate,
        $interviewType,
        $interviewLink,
        $interviewInstructions,
        $applicationId
    ]);

    // Send notifications
    NotificationService::notifyStatusChange($db, (int) $app['user_id'], $app['title'], 'interview_scheduled');
    logActivity($db, (int) $_SESSION['user_id'], 'schedule_interview', "Scheduled interview for App #{$applicationId}");

    // Send the interview schedule email
    $formattedDate = date('d M Y, h:i A', strtotime($interviewDate));
    MailService::sendInterviewScheduled(
        $app['candidate_email'],
        $app['candidate_name'],
        $app['title'],
        $app['company_name'],
        $formattedDate,
        $interviewType,
        $interviewLink,
        $interviewInstructions
    );

    echo json_encode(['success' => true, 'message' => 'Interview scheduled successfully and applicant notified!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
