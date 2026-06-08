<?php if (empty($dashboardLayout)): ?>
<!-- ══════════════════════ FOOTER ══════════════════════ -->
<footer style="background:var(--ft-surface);border-top:1px solid var(--ft-border);padding:3rem 0 2rem;margin-top:auto;">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <a href="<?= url('index.php') ?>" class="d-flex align-items-center gap-2 mb-3 text-decoration-none">
                    <div style="width:32px;height:32px;border-radius:8px;background:var(--grad-blue-cyan);display:flex;align-items:center;justify-content:center;color:#fff;">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                    <span style="font-family:'Outfit',sans-serif;font-weight:900;font-size:1.1rem;background:var(--grad-blue-cyan);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">SmartHire Pro</span>
                </a>
                <p style="font-size:0.875rem;color:var(--ft-muted);line-height:1.65;max-width:280px;">
                    Connecting exceptional talent with outstanding opportunities. Build your dream career or find your next hire.
                </p>
                <div class="d-flex gap-2 mt-3">
                    <?php foreach(['linkedin','twitter-x','github','instagram'] as $s): ?>
                    <a href="#" style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,0.04);border:1px solid var(--ft-border);display:flex;align-items:center;justify-content:center;color:var(--ft-muted);font-size:0.85rem;transition:var(--transition-fast);"
                       onmouseover="this.style.color='var(--ft-primary)';this.style.borderColor='rgba(59,130,246,0.3)';"
                       onmouseout="this.style.color='var(--ft-muted)';this.style.borderColor='var(--ft-border)';">
                        <i class="bi bi-<?= $s ?>"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <p style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--ft-muted-d);margin-bottom:1rem;">For Job Seekers</p>
                <?php foreach(['Browse Jobs' => 'index.php', 'Create Profile' => 'auth/register.php', 'Saved Jobs' => 'jobseeker/saved-jobs.php', 'My Applications' => 'jobseeker/applications.php'] as $label => $path): ?>
                <div class="mb-2"><a href="<?= url($path) ?>" style="font-size:0.875rem;color:var(--ft-muted);text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='var(--ft-primary)';" onmouseout="this.style.color='var(--ft-muted)';"><?= $label ?></a></div>
                <?php endforeach; ?>
            </div>
            <div class="col-6 col-md-2">
                <p style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--ft-muted-d);margin-bottom:1rem;">For Recruiters</p>
                <?php foreach(['Post a Job' => 'auth/register.php', 'Recruiter Login' => 'auth/login.php', 'Manage Jobs' => 'recruiter/jobs/index.php', 'View Applicants' => 'recruiter/dashboard.php'] as $label => $path): ?>
                <div class="mb-2"><a href="<?= url($path) ?>" style="font-size:0.875rem;color:var(--ft-muted);text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='var(--ft-primary)';" onmouseout="this.style.color='var(--ft-muted)';"><?= $label ?></a></div>
                <?php endforeach; ?>
            </div>
            <div class="col-md-4">
                <p style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--ft-muted-d);margin-bottom:1rem;">Stay Updated</p>
                <p style="font-size:0.875rem;color:var(--ft-muted);margin-bottom:1rem;">Get the latest job alerts and career tips.</p>
                <div class="d-flex gap-2">
                    <input type="email" placeholder="Enter your email" style="flex:1;background:rgba(255,255,255,0.04);border:1px solid var(--ft-border);border-radius:8px;padding:0.6rem 0.9rem;color:var(--ft-text);font-size:0.875rem;outline:none;" onfocus="this.style.borderColor='var(--ft-primary)';" onblur="this.style.borderColor='var(--ft-border)';">
                    <button class="btn btn-primary btn-sm" style="white-space:nowrap;border-radius:8px;">Subscribe</button>
                </div>
            </div>
        </div>
        <div style="border-top:1px solid var(--ft-border);padding-top:1.5rem;display:flex;flex-wrap:wrap;gap:1rem;justify-content:space-between;align-items:center;">
            <p style="font-size:0.82rem;color:var(--ft-muted-d);margin:0;">
                &copy; <?= date('Y') ?> SmartHire Pro. All rights reserved.
            </p>
            <div class="d-flex gap-3">
                <?php foreach(['Privacy Policy','Terms of Service','Cookie Policy'] as $l): ?>
                <a href="#" style="font-size:0.82rem;color:var(--ft-muted-d);text-decoration:none;" onmouseover="this.style.color='var(--ft-primary)';" onmouseout="this.style.color='var(--ft-muted-d)';"><?= $l ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</footer>
<?php endif; ?>

<script>window.SHP_BASE_URL = <?= json_encode(rtrim(BASE_URL, '/')) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/dark-mode.js') ?>"></script>
<script src="<?= asset('js/main.js') ?>"></script>
<?php foreach ($extraJs as $js): ?>
<script src="<?= e($js) ?>"></script>
<?php endforeach; ?>
</body>
</html>
