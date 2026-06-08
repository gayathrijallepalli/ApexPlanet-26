<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['jobseeker']);

$userId = (int) $_SESSION['user_id'];
$stmt = $db->prepare(
    'SELECT a.*, j.title, j.job_type, j.location AS job_location, c.name AS company_name
     FROM applications a
     JOIN jobs j ON j.id = a.job_id
     JOIN companies c ON c.id = j.company_id
     WHERE a.user_id = ? ORDER BY a.applied_at DESC'
);
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();

$stages = [
    'applied' => 'Applied',
    'under_review' => 'Under Review',
    'shortlisted' => 'Shortlisted',
    'interview_scheduled' => 'Interviewing',
    'selected' => 'Offer Extended',
];

$pageTitle = 'My Applications';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="mb-4">
    <h1 class="page-heading">My Applications</h1>
    <p class="page-subtitle">Track your candidacy phases and review scheduling details.</p>
</div>

<?php if (!$applications): ?>
    <div class="glass-card text-center py-5">
        <div class="text-muted mb-3"><i class="bi bi-archive-fill" style="font-size: 3rem; opacity: 0.3;"></i></div>
        <h5 class="fw-bold">No Applications Found</h5>
        <p class="text-muted mb-4">You haven't applied to any job openings yet.</p>
        <a href="<?= url('jobseeker/jobs.php') ?>" class="btn btn-primary btn-gradient hover-lift">Find & Apply for Jobs</a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($applications as $app): 
            $currStatus = $app['status'];
            $isRejected = $currStatus === 'rejected';
        ?>
            <div class="col-12">
                <div class="glass-card p-4 transition-base hover-lift">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary mb-2 rounded-pill px-3 py-1" style="font-size:0.75rem;">
                                <?= ucfirst(str_replace('-', ' ', $app['job_type'])) ?>
                            </span>
                            <h4 class="h5 fw-bold mb-1"><a href="<?= url('jobseeker/job-detail.php?id=' . $app['job_id']) ?>" class="text-decoration-none text-white"><?= e($app['title']) ?></a></h4>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-building me-1"></i><?= e($app['company_name']) ?> 
                                &middot; <i class="bi bi-geo-alt me-1"></i><?= e($app['job_location']) ?>
                                &middot; <i class="bi bi-clock me-1"></i>Applied on <?= formatDate($app['applied_at']) ?>
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?= statusBadge($currStatus) ?>
                            <a href="<?= url('jobseeker/job-detail.php?id=' . $app['job_id']) ?>" class="btn btn-sm btn-outline-primary hover-lift"><i class="bi bi-eye me-1"></i>View Job</a>
                        </div>
                    </div>

                    <?php if ($isRejected): ?>
                        <div class="alert alert-danger py-2 px-3 small border-0 bg-danger bg-opacity-10 text-danger rounded-3 mt-3 mb-0">
                            <i class="bi bi-x-circle-fill me-2"></i>We appreciate your application but have chosen to move forward with other candidates.
                        </div>
                    <?php else: ?>
                        <!-- Step Tracker Progress Bar -->
                        <div class="position-relative py-3 mt-3 mb-4 mx-2">
                            <div class="progress" style="height: 4px; background: rgba(255,255,255,0.06); border-radius: 99px;">
                                <?php 
                                    $stagesKeys = array_keys($stages);
                                    $currIndex = array_search($currStatus, $stagesKeys, true);
                                    $percent = ($currIndex !== false) ? ($currIndex / (count($stages) - 1)) * 100 : 0;
                                ?>
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percent ?>%; transition: width 1.2s ease;"></div>
                            </div>
                            <div class="d-flex justify-content-between position-absolute w-100 top-50 translate-middle-y">
                                <?php 
                                $stepIdx = 0;
                                foreach ($stages as $key => $label): 
                                    $isDone = ($currIndex !== false && $stepIdx <= $currIndex);
                                    $isActive = ($currStatus === $key);
                                ?>
                                    <div class="d-flex flex-column align-items-center" style="width: 20px;">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 14px; height: 14px; background: <?= $isActive ? 'var(--ft-primary)' : ($isDone ? 'var(--ft-secondary)' : '#1E293B') ?>; 
                                                    border: 2px solid <?= $isActive || $isDone ? 'transparent' : 'rgba(255,255,255,0.1)' ?>; 
                                                    box-shadow: <?= $isActive ? '0 0 10px var(--ft-primary)' : 'none' ?>;
                                                    transition: all 0.3s;">
                                        </div>
                                        <span class="d-none d-md-block text-muted mt-2" style="font-size: 0.65rem; white-space: nowrap; font-weight: <?= $isActive ? '700' : '500' ?>; color: <?= $isActive ? 'var(--ft-text) !important' : ($isDone ? '#f8fafc' : '') ?>;">
                                            <?= $label ?>
                                        </span>
                                    </div>
                                <?php 
                                $stepIdx++;
                                endforeach; ?>
                            </div>
                        </div>
                        <div style="height: 12px;" class="d-block d-md-none"></div>
                    <?php endif; ?>

                    <!-- Interview Scheduled Details Widget -->
                    <?php if ($currStatus === 'interview_scheduled' && $app['interview_date']): ?>
                        <div class="mt-3 p-3 bg-white bg-opacity-5 rounded-3 border border-primary border-opacity-10 d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-15 text-primary d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="bi bi-camera-video-fill"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-white fw-bold">Interview Scheduled</h6>
                                    <p class="text-muted small mb-0">
                                        <span class="text-white"><i class="bi bi-calendar-event me-1 text-primary"></i><?= date('d M Y, h:i A', strtotime($app['interview_date'])) ?></span>
                                        &middot; <span><i class="bi bi-laptop me-1 text-primary"></i><?= str_replace('_', ' ', ucfirst($app['interview_type'])) ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($app['interview_link']): ?>
                                    <a href="<?= e($app['interview_link']) ?>" target="_blank" class="btn btn-sm btn-primary hover-lift"><i class="bi bi-camera-video me-1"></i>Join Call</a>
                                <?php endif; ?>
                                <?php if ($app['interview_instructions']): ?>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#instructions-<?= $app['id'] ?>"><i class="bi bi-info-circle me-1"></i>Instructions</button>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($app['interview_instructions']): ?>
                                <div class="collapse w-100 mt-2" id="instructions-<?= $app['id'] ?>">
                                    <div class="p-3 bg-white bg-opacity-5 rounded-3 border-0 small text-muted">
                                        <strong>Recruiter Instructions:</strong><br>
                                        <?= nl2br(htmlspecialchars($app['interview_instructions'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
