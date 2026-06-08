<?php

class AnalyticsService
{
    public static function getDashboardStats(PDO $db): array
    {
        return [
            'total_users' => (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'total_recruiters' => (int) $db->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = 'recruiter'")->fetchColumn(),
            'total_jobs' => (int) $db->query('SELECT COUNT(*) FROM jobs')->fetchColumn(),
            'total_applications' => (int) $db->query('SELECT COUNT(*) FROM applications')->fetchColumn(),
            'active_companies' => (int) $db->query("SELECT COUNT(*) FROM companies WHERE approval_status = 'approved'")->fetchColumn(),
        ];
    }

    public static function userGrowth(PDO $db): array
    {
        $stmt = $db->query(
            "SELECT DATE_FORMAT(created_at, '%b %Y') AS label, COUNT(*) AS total
             FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m'), label ORDER BY MIN(created_at)"
        );
        $rows = $stmt->fetchAll();
        return ['labels' => array_column($rows, 'label'), 'data' => array_map('intval', array_column($rows, 'total'))];
    }

    public static function applicationsPerMonth(PDO $db): array
    {
        $stmt = $db->query(
            "SELECT DATE_FORMAT(applied_at, '%b %Y') AS label, COUNT(*) AS total
             FROM applications WHERE applied_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(applied_at, '%Y-%m'), label ORDER BY MIN(applied_at)"
        );
        $rows = $stmt->fetchAll();
        return ['labels' => array_column($rows, 'label'), 'data' => array_map('intval', array_column($rows, 'total'))];
    }

    public static function jobsPerMonth(PDO $db): array
    {
        $stmt = $db->query(
            "SELECT DATE_FORMAT(created_at, '%b %Y') AS label, COUNT(*) AS total
             FROM jobs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m'), label ORDER BY MIN(created_at)"
        );
        $rows = $stmt->fetchAll();
        return ['labels' => array_column($rows, 'label'), 'data' => array_map('intval', array_column($rows, 'total'))];
    }

    public static function topHiringCompanies(PDO $db): array
    {
        $stmt = $db->query(
            'SELECT c.name AS label, COUNT(a.id) AS total
             FROM companies c
             JOIN jobs j ON j.company_id = c.id
             JOIN applications a ON a.job_id = j.id
             GROUP BY c.id, c.name ORDER BY total DESC LIMIT 8'
        );
        $rows = $stmt->fetchAll();
        return ['labels' => array_column($rows, 'label'), 'data' => array_map('intval', array_column($rows, 'total'))];
    }
}
