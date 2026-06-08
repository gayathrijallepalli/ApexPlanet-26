<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['jobseeker']);
require_once BASE_PATH . '/services/ProfileStrengthService.php';

$userId = (int) $_SESSION['user_id'];
$stmt = $db->prepare('SELECT * FROM job_seeker_profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: ['user_id' => $userId];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $education = trim($_POST['education'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');

    $photo = $profile['photo'] ?? null;
    $resume = $profile['resume_path'] ?? null;

    if (!empty($_FILES['photo']['name'])) {
        $uploaded = uploadFile($_FILES['photo'], 'photos', ALLOWED_IMAGE_TYPES);
        if ($uploaded) $photo = $uploaded;
    }
    if (!empty($_FILES['resume']['name'])) {
        $uploaded = uploadFile($_FILES['resume'], 'resumes', ALLOWED_RESUME_TYPES);
        if ($uploaded) $resume = $uploaded;
    }

    $updatedProfile = array_merge($profile, [
        'photo' => $photo,
        'resume_path' => $resume,
        'education' => $education,
        'skills' => $skills,
        'experience' => $experience,
        'phone' => $phone,
        'location' => $location,
    ]);
    $strength = ProfileStrengthService::calculate($updatedProfile);

    if (!empty($profile['id'])) {
        $stmt = $db->prepare(
            'UPDATE job_seeker_profiles SET photo=?, phone=?, location=?, education=?, skills=?, experience=?, resume_path=?, profile_strength=? WHERE user_id=?'
        );
        $stmt->execute([$photo, $phone, $location, $education, $skills, $experience, $resume, $strength, $userId]);
    } else {
        $stmt = $db->prepare(
            'INSERT INTO job_seeker_profiles (user_id, photo, phone, location, education, skills, experience, resume_path, profile_strength) VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([$userId, $photo, $phone, $location, $education, $skills, $experience, $resume, $strength]);
    }

    $fullName = trim($_POST['full_name'] ?? '');
    if ($fullName) {
        $db->prepare('UPDATE users SET full_name = ? WHERE id = ?')->execute([$fullName, $userId]);
        $_SESSION['user']['full_name'] = $fullName;
    }

    logActivity($db, $userId, 'update_profile', 'Profile updated');
    setFlash('success', 'Profile updated successfully.');
    redirect(url('jobseeker/profile.php'));
}

$stmt = $db->prepare('SELECT full_name FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
$breakdown = ProfileStrengthService::breakdown($profile);
$strength = ProfileStrengthService::calculate($profile);

$pageTitle = 'My Profile';
$extraJs = [asset('js/dashboard.js')];
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="mb-4">
    <h1 class="page-heading">My Profile</h1>
    <p class="page-subtitle">Configure your developer metadata, verify details, and manage your CV.</p>
</div>

<div class="row g-4">
    <!-- ── LEFT PROFILE SIDEBAR ── -->
    <div class="col-lg-4">
        <!-- Profile Pic & Avatar Strength -->
        <div class="glass-card p-4 text-center mb-4">
            <div class="position-relative d-inline-block mb-3">
                <?php if (!empty($profile['photo'])): ?>
                    <img src="<?= url($profile['photo']) ?>" alt="Avatar" class="rounded-circle border border-2 border-primary border-opacity-30 shadow" style="width: 110px; height: 110px; object-fit: cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center border border-2 border-primary border-opacity-10 mx-auto" style="width: 110px; height: 110px; font-size: 2.5rem; font-weight: 700;">
                        <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <h5 class="fw-bold mb-1"><?= e($user['full_name'] ?? 'User') ?></h5>
            <p class="text-muted small mb-4"><i class="bi bi-geo-alt me-1"></i><?= e($profile['location'] ?? 'Not set') ?></p>
            
            <!-- Circular Profile Strength Meter -->
            <div class="circular-gauge-wrapper mb-4">
                <svg class="progress-ring" width="110" height="110">
                    <defs>
                        <linearGradient id="profileGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#3b82f6" />
                            <stop offset="100%" stop-color="#06b6d4" />
                        </linearGradient>
                    </defs>
                    <circle stroke="rgba(255,255,255,0.05)" stroke-width="8" fill="transparent" r="46" cx="55" cy="55"/>
                    <circle class="progress-ring__circle" stroke="url(#profileGrad)" stroke-width="8" fill="transparent" r="46" cx="55" cy="55" stroke-linecap="round" data-percent="<?= $strength ?>"/>
                </svg>
                <div class="gauge-percentage" style="font-size:1.35rem;">
                    <?= $strength ?>%
                    <span>strength</span>
                </div>
            </div>

            <div class="text-start mt-2">
                <h6 class="text-white small fw-bold mb-3">Integrity Checklist</h6>
                <?php foreach ($breakdown as $item): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white bg-opacity-5 rounded-2" style="font-size:0.8rem;">
                        <span class="text-muted"><?= e($item['label']) ?></span>
                        <span class="badge <?= $item['done'] ? 'bg-success bg-opacity-15 text-success' : 'bg-white bg-opacity-5 text-muted' ?>" style="font-size:0.7rem; font-weight:700;">
                            <?= $item['done'] ? '✓ Ready' : 'Missing' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Resume Download Widget -->
        <?php if (!empty($profile['resume_path'])): ?>
            <div class="glass-card p-4 text-center">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3 text-start">
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-white fw-bold">Resume Uploaded</h6>
                            <span class="text-muted small">Valid PDF document</span>
                        </div>
                    </div>
                    <a href="<?= url($profile['resume_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary hover-lift"><i class="bi bi-download"></i></a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── PROFILE EDIT FORM ── -->
    <div class="col-lg-8">
        <div class="glass-card p-4">
            <h5 class="fw-bold mb-4"><i class="bi bi-pencil-square me-2 text-primary"></i>Profile Details</h5>
            <form method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($profile['phone'] ?? '') ?>" placeholder="+91 XXXXX XXXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Location</label>
                        <input type="text" name="location" class="form-control" value="<?= e($profile['location'] ?? '') ?>" placeholder="Bangalore, India">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Avatar Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Academic Background (Education)</label>
                        <textarea name="education" class="form-control" rows="3" placeholder="Describe your degree, major, and graduation details..."><?= e($profile['education'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Professional Experience</label>
                        <textarea name="experience" class="form-control" rows="3" placeholder="Describe your job titles, organizations, and project summary..."><?= e($profile['experience'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Technical Skills (Comma separated list)</label>
                        <input type="text" name="skills" class="form-control" value="<?= e($profile['skills'] ?? '') ?>" placeholder="React, Node.js, PHP, Laravel, TypeScript">
                        <div class="form-text text-muted" style="font-size:0.75rem;">Separate key technologies with commas so our matching engine processes them.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Resume Document (PDF Format)</label>
                        <input type="file" name="resume" class="form-control" accept=".pdf">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary btn-gradient hover-lift px-4"><i class="bi bi-save me-2"></i>Save Details</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
