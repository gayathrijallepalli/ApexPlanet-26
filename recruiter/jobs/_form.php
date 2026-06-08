<?php
$job = $job ?? [];
$isEdit = !empty($job['id']);
?>
<div class="glass-card p-4">
    <h5 class="fw-bold text-white mb-4"><i class="bi bi-file-earmark-plus text-primary me-2"></i><?= $isEdit ? 'Edit Opportunity Details' : 'Post New Opportunity' ?></h5>
    <form method="POST">
        <?= csrfField() ?>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label text-muted small">Job Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Senior Full-Stack Architect" required value="<?= e($job['title'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Job Type *</label>
                <select name="job_type" class="form-select">
                    <?php foreach (['full-time','part-time','contract','internship','remote'] as $t): ?>
                    <option value="<?= $t ?>" <?= ($job['job_type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Location *</label>
                <input type="text" name="location" class="form-control" placeholder="e.g. Bangalore, India or Remote" required value="<?= e($job['location'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Experience Level</label>
                <select name="experience_level" class="form-select">
                    <option value="">Any</option>
                    <?php foreach (['Entry','Mid','Senior'] as $e): ?>
                    <option value="<?= $e ?>" <?= ($job['experience_level'] ?? '') === $e ? 'selected' : '' ?>><?= $e ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Status</label>
                <select name="status" class="form-select">
                    <?php foreach (['active','draft','closed'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($job['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small">Salary Min (CTC in INR)</label>
                <input type="number" name="salary_min" class="form-control" placeholder="e.g. 800000" value="<?= e($job['salary_min'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small">Salary Max (CTC in INR)</label>
                <input type="number" name="salary_max" class="form-control" placeholder="e.g. 1500000" value="<?= e($job['salary_max'] ?? '') ?>">
            </div>
            <div class="col-12">
                <label class="form-label text-muted small">Role Description *</label>
                <textarea name="description" class="form-control" rows="6" placeholder="Provide a detailed description of the role, daily responsibilities, and team structure..." required><?= e($job['description'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label text-muted small">Skills & Requirements (one per line recommended)</label>
                <textarea name="requirements" class="form-control" rows="4" placeholder="e.g. 5+ years of experience with React/Node.js&#10;Familiarity with AWS (S3, EC2, CloudFront)&#10;Solid understanding of relational database optimization"><?= e($job['requirements'] ?? '') ?></textarea>
            </div>
        </div>
        
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= url('recruiter/jobs/index.php') ?>" class="btn btn-outline-primary hover-lift px-4">Cancel</a>
            <button type="submit" class="btn btn-primary btn-gradient hover-lift px-4"><?= $isEdit ? 'Save Changes' : 'Publish Job' ?></button>
        </div>
    </form>
</div>
