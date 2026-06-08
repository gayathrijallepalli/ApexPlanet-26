<?php
require_once __DIR__ . '/../includes/init.php';
require_once BASE_PATH . '/services/AnalyticsService.php';
header('Content-Type: application/json');

requireLogin();
requireRole(['admin']);

echo json_encode([
    'success' => true,
    'charts' => [
        'userGrowth' => AnalyticsService::userGrowth($db),
        'applicationsPerMonth' => AnalyticsService::applicationsPerMonth($db),
        'jobsPerMonth' => AnalyticsService::jobsPerMonth($db),
        'topCompanies' => AnalyticsService::topHiringCompanies($db),
    ],
]);
