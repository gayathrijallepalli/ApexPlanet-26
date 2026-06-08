<?php

class NotificationService
{
    public static function create(PDO $db, int $userId, string $type, string $title, string $message, ?string $link = null): void
    {
        $stmt = $db->prepare(
            'INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $type, $title, $message, $link]);
    }

    public static function notifyApplicationSubmitted(PDO $db, int $seekerId, int $recruiterId, string $jobTitle, int $jobId): void
    {
        self::create(
            $db,
            $seekerId,
            'application_submitted',
            'Application Submitted',
            "Your application for \"{$jobTitle}\" has been submitted successfully.",
            url('jobseeker/applications.php')
        );
        self::create(
            $db,
            $recruiterId,
            'new_application',
            'New Application',
            "A new candidate applied for \"{$jobTitle}\".",
            url('recruiter/jobs/applicants.php?job_id=' . $jobId)
        );
    }

    public static function notifyStatusChange(PDO $db, int $seekerId, string $jobTitle, string $status): void
    {
        $messages = [
            'under_review' => 'Your application is now under review.',
            'shortlisted' => 'Congratulations! You have been shortlisted.',
            'interview_scheduled' => 'An interview has been scheduled for your application.',
            'selected' => 'Congratulations! You have been selected for the position.',
            'rejected' => 'Your application status has been updated to rejected.',
        ];
        $msg = $messages[$status] ?? 'Your application status has been updated.';
        self::create(
            $db,
            $seekerId,
            'status_' . $status,
            'Application Update: ' . ucwords(str_replace('_', ' ', $status)),
            "\"{$jobTitle}\" — {$msg}",
            url('jobseeker/applications.php')
        );
    }

    public static function getUnreadCount(PDO $db, int $userId): int
    {
        $stmt = $db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function getRecent(PDO $db, int $userId, int $limit = 10): array
    {
        $stmt = $db->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
