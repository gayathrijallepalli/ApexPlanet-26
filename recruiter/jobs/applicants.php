<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole(['recruiter']);
require_once BASE_PATH . '/services/ApplicationService.php';

$userId = (int) $_SESSION['user_id'];
$jobId = (int) ($_GET['job_id'] ?? 0);

$stmt = $db->prepare('SELECT j.* FROM jobs j WHERE j.id = ? AND j.posted_by = ?');
$stmt->execute([$jobId, $userId]);
$job = $stmt->fetch();
if (!$job) {
    setFlash('danger', 'Job not found.');
    redirect(url('recruiter/jobs/index.php'));
}

// Check POST status update fallback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['status']) && !isset($_POST['interview_date'])) {
    requireCsrf();
    $result = ApplicationService::updateStatus($db, (int) $_POST['application_id'], $userId, $_POST['status'], trim($_POST['notes'] ?? ''));
    setFlash($result['success'] ? 'success' : 'danger', $result['message']);
    redirect(url('recruiter/jobs/applicants.php?job_id=' . $jobId));
}

$stmt = $db->prepare(
    'SELECT a.*, u.full_name, u.email, p.phone, p.resume_path, p.skills, p.experience
     FROM applications a
     JOIN users u ON u.id = a.user_id
     LEFT JOIN job_seeker_profiles p ON p.user_id = u.id
     WHERE a.job_id = ? ORDER BY a.applied_at DESC'
);
$stmt->execute([$jobId]);
$applicants = $stmt->fetchAll();

$stages = [
    'applied' => 'Applied',
    'under_review' => 'Under Review',
    'shortlisted' => 'Shortlisted',
    'interview_scheduled' => 'Interviewing',
    'selected' => 'Offer Extended',
    'rejected' => 'Rejected',
];

$grouped = [];
foreach ($stages as $key => $label) {
    $grouped[$key] = [];
}
foreach ($applicants as $app) {
    $grouped[$app['status']][] = $app;
}

$pageTitle = 'Applicants - ' . $job['title'];
$extraJs = [asset('js/dashboard.js')];
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="page-heading">Applicants: <?= e($job['title']) ?></h1>
        <p class="page-subtitle">Process candidates through the recruitment stages and schedule assessments.</p>
    </div>
    <a href="<?= url('recruiter/jobs/index.php') ?>" class="btn btn-outline-primary hover-lift"><i class="bi bi-arrow-left-short me-1"></i>Back to Jobs</a>
</div>

<!-- ── KANBAN BOARD ── -->
<div class="kanban-board">
    <?php foreach ($stages as $statusKey => $statusLabel): 
        $list = $grouped[$statusKey];
        $count = count($list);
        
        $headerColor = 'var(--ft-muted)';
        if ($statusKey === 'shortlisted') $headerColor = 'var(--ft-primary)';
        if ($statusKey === 'interview_scheduled') $headerColor = 'var(--ft-warning)';
        if ($statusKey === 'selected') $headerColor = 'var(--ft-success)';
        if ($statusKey === 'rejected') $headerColor = 'var(--ft-danger)';
    ?>
        <div class="kanban-column">
            <div class="kanban-column-header">
                <span class="fw-bold text-white small" style="border-left: 3px solid <?= $headerColor ?>; padding-left: 8px;">
                    <?= $statusLabel ?>
                </span>
                <span class="badge bg-white bg-opacity-5 text-muted rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                    <?= $count ?>
                </span>
            </div>
            
            <div class="kanban-cards">
                <?php if (empty($list)): ?>
                    <div class="text-center py-4 text-muted small" style="border: 1px dashed rgba(255,255,255,0.05); border-radius: 8px;">
                        No candidates
                    </div>
                <?php else: ?>
                    <?php foreach ($list as $app): 
                        $initials = strtoupper(substr($app['full_name'] ?? 'C', 0, 1) . (strpos($app['full_name'] ?? '', ' ') ? substr(explode(' ', $app['full_name'])[1], 0, 1) : ''));
                    ?>
                        <div class="kanban-card">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold text-white" 
                                         style="width: 32px; height: 32px; font-size: 0.8rem; background: var(--grad-blue-cyan);">
                                        <?= $initials ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-white" style="font-size:0.875rem;"><?= e($app['full_name']) ?></h6>
                                        <span class="text-muted" style="font-size:0.7rem;"><?= date('d M Y', strtotime($app['applied_at'])) ?></span>
                                    </div>
                                </div>
                                
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill" style="font-size:0.65rem;">
                                    <i class="bi bi-stars text-warning me-1"></i>85%
                                </span>
                            </div>

                            <p class="text-muted small mb-3 text-truncate-2" style="font-size:0.75rem; line-height: 1.45;">
                                <strong>Skills:</strong> <?= e($app['skills'] ?: 'Not set') ?>
                            </p>

                            <!-- Candidate CV & Contacts -->
                            <div class="d-flex gap-2 justify-content-between align-items-center mb-3 pt-2 border-top border-white border-opacity-5">
                                <?php if ($app['resume_path']): ?>
                                    <a href="<?= url($app['resume_path']) ?>" target="_blank" class="text-primary small text-decoration-none" style="font-size:0.75rem;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Open Resume
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small" style="font-size:0.75rem;"><i class="bi bi-file-earmark-slash me-1"></i>No Resume</span>
                                <?php endif; ?>
                            </div>

                            <!-- Stage Action Controls -->
                            <div class="d-flex gap-1 justify-content-end mt-2">
                                <?php if ($statusKey === 'applied'): ?>
                                    <button class="btn btn-sm btn-outline-primary py-1" data-action="update-status" data-application-id="<?= $app['id'] ?>" data-status="under_review">
                                        Review <i class="bi bi-chevron-right-short"></i>
                                    </button>
                                <?php elseif ($statusKey === 'under_review'): ?>
                                    <button class="btn btn-sm btn-outline-danger py-1" data-action="update-status" data-application-id="<?= $app['id'] ?>" data-status="rejected">
                                        <i class="bi bi-x"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary py-1" data-action="update-status" data-application-id="<?= $app['id'] ?>" data-status="shortlisted">
                                        Shortlist <i class="bi bi-chevron-right-short"></i>
                                    </button>
                                <?php elseif ($statusKey === 'shortlisted'): ?>
                                    <button class="btn btn-sm btn-outline-danger py-1" data-action="update-status" data-application-id="<?= $app['id'] ?>" data-status="rejected">
                                        <i class="bi bi-x"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary btn-gradient py-1" data-bs-toggle="modal" data-bs-target="#interviewModal" data-application-id="<?= $app['id'] ?>">
                                        <i class="bi bi-calendar-event me-1"></i>Schedule
                                    </button>
                                <?php elseif ($statusKey === 'interview_scheduled'): ?>
                                    <button class="btn btn-sm btn-outline-danger py-1" data-action="update-status" data-application-id="<?= $app['id'] ?>" data-status="rejected">
                                        <i class="bi bi-x"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success py-1" data-action="update-status" data-application-id="<?= $app['id'] ?>" data-status="selected">
                                        Extend Offer <i class="bi bi-check-lg"></i>
                                    </button>
                                <?php elseif ($statusKey === 'selected'): ?>
                                    <span class="text-success small"><i class="bi bi-check2-all me-1"></i>Offer Placed</span>
                                <?php else: ?>
                                    <span class="text-muted small"><i class="bi bi-slash-circle me-1"></i>Candidacy Closed</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- CSRF Validation Placeholder for script -->
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

<!-- ── INTERVIEW SCHEDULER MODAL ── -->
<div class="modal fade" id="interviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="scheduleInterviewForm" class="modal-content glass-card p-3" style="background: var(--ft-surface); border: 1px solid var(--ft-border);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white">Schedule Candidate Interview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="application_id" value="">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                
                <div class="mb-3">
                    <label class="form-label text-muted small">Date & Time *</label>
                    <input type="datetime-local" name="interview_date" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted small">Assessment Type *</label>
                    <select name="interview_type" class="form-select">
                        <option value="google_meet">Google Meet Video Call</option>
                        <option value="zoom">Zoom Video Call</option>
                        <option value="teams">Microsoft Teams Meeting</option>
                        <option value="in_person">In-Person Office Assessment</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted small">Session Link / Office Address URL</label>
                    <input type="url" name="interview_link" class="form-control" placeholder="https://meet.google.com/abc-defg-hij">
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted small">Instructions for Candidate</label>
                    <textarea name="interview_instructions" class="form-control" rows="3" placeholder="Provide link details, assessment parameters, or guidelines..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-gradient hover-lift">Schedule & Notify</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
