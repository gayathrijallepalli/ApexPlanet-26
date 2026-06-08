<?php

require_once __DIR__ . '/NotificationService.php';

class ApplicationService
{
    private static array $validTransitions = [
        'applied' => ['under_review', 'rejected'],
        'under_review' => ['shortlisted', 'rejected'],
        'shortlisted' => ['interview_scheduled', 'rejected'],
        'interview_scheduled' => ['selected', 'rejected'],
        'selected' => [],
        'rejected' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::$validTransitions[$from] ?? [], true);
    }

    public static function apply(PDO $db, int $userId, int $jobId, ?string $coverLetter = null): array
    {
        $stmt = $db->prepare(
            'SELECT j.id, j.title, j.status, j.company_id, c.name AS company_name, c.recruiter_id
             FROM jobs j JOIN companies c ON c.id = j.company_id
             WHERE j.id = ? AND j.status = ? AND c.approval_status = ?'
        );
        $stmt->execute([$jobId, 'active', 'approved']);
        $job = $stmt->fetch();
        if (!$job) {
            return ['success' => false, 'message' => 'Job not available.'];
        }

        $stmt = $db->prepare('SELECT id FROM applications WHERE job_id = ? AND user_id = ?');
        $stmt->execute([$jobId, $userId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'You have already applied for this job.'];
        }

        $stmt = $db->prepare(
            'INSERT INTO applications (job_id, user_id, cover_letter, status) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$jobId, $userId, $coverLetter, 'applied']);

        NotificationService::notifyApplicationSubmitted($db, $userId, (int) $job['recruiter_id'], $job['title'], $jobId);
        logActivity($db, $userId, 'apply_job', "Applied for job #{$jobId}");

        // Send submission confirmation email
        $userStmt = $db->prepare('SELECT email, full_name FROM users WHERE id = ?');
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch();
        if ($user) {
            require_once BASE_PATH . '/services/MailService.php';
            MailService::sendApplicationSubmitted($user['email'], $user['full_name'], $job['title'], $job['company_name']);
        }

        return ['success' => true, 'message' => 'Application submitted successfully!'];
    }

    public static function updateStatus(PDO $db, int $applicationId, int $recruiterId, string $newStatus, ?string $notes = null): array
    {
        $stmt = $db->prepare(
            'SELECT a.*, j.title, j.posted_by, c.name AS company_name, c.recruiter_id
             FROM applications a
             JOIN jobs j ON j.id = a.job_id
             JOIN companies c ON c.id = j.company_id
             WHERE a.id = ?'
        );
        $stmt->execute([$applicationId]);
        $app = $stmt->fetch();
        if (!$app || (int) $app['recruiter_id'] !== $recruiterId) {
            return ['success' => false, 'message' => 'Application not found.'];
        }
        if (!self::canTransition($app['status'], $newStatus)) {
            return ['success' => false, 'message' => 'Invalid status transition.'];
        }

        $stmt = $db->prepare('UPDATE applications SET status = ?, notes = ? WHERE id = ?');
        $stmt->execute([$newStatus, $notes, $applicationId]);

        NotificationService::notifyStatusChange($db, (int) $app['user_id'], $app['title'], $newStatus);
        logActivity($db, $recruiterId, 'update_application', "App #{$applicationId} → {$newStatus}");

        // Send status update email to applicant
        $userStmt = $db->prepare('SELECT email, full_name FROM users WHERE id = ?');
        $userStmt->execute([$app['user_id']]);
        $user = $userStmt->fetch();
        if ($user) {
            require_once BASE_PATH . '/services/MailService.php';
            if ($newStatus === 'shortlisted') {
                MailService::sendShortlisted($user['email'], $user['full_name'], $app['title'], $app['company_name']);
            } elseif ($newStatus === 'selected') {
                MailService::sendSelected($user['email'], $user['full_name'], $app['title'], $app['company_name']);
            } elseif ($newStatus === 'rejected') {
                MailService::sendRejected($user['email'], $user['full_name'], $app['title'], $app['company_name']);
            }
        }

        return ['success' => true, 'message' => 'Application status updated.'];
    }
}
