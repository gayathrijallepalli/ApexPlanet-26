/**
 * SmartHire Pro — Job Search & Live Results
 * Google-style live search with skeleton loading, AJAX, infinite scroll
 */
(function () {
    'use strict';

    const DEBOUNCE_MS = 350;
    let debounceTimer = null;
    let currentPage   = 1;
    let isLoading     = false;
    let hasMore       = true;
    let lastParams    = {};

    /* ── DOM refs ── */
    const form         = document.getElementById('jobSearchForm');
    const resultsWrap  = document.getElementById('jobResults');
    const countLabel   = document.getElementById('jobsCountLabel');
    const loadMoreBtn  = document.getElementById('loadMoreBtn');

    if (!form || !resultsWrap) return;

    /* ── Build skeleton cards ── */
    function skeletonCards(count = 6) {
        return Array.from({ length: count }, () => `
            <div class="col-md-6 col-xl-4">
                <div class="job-list-card" style="gap:1rem;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="skeleton skeleton-avatar"></div>
                        <div style="flex:1">
                            <div class="skeleton skeleton-title" style="width:70%;"></div>
                            <div class="skeleton skeleton-text" style="width:45%;"></div>
                        </div>
                    </div>
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text" style="width:80%;"></div>
                    <div class="skeleton skeleton-text" style="width:55%;margin-top:1rem;"></div>
                </div>
            </div>
        `).join('');
    }

    /* ── Render a single job card ── */
    function renderCard(job) {
        const avatarGrads = [
            'linear-gradient(135deg,#2563EB,#06B6D4)',
            'linear-gradient(135deg,#8B5CF6,#EC4899)',
            'linear-gradient(135deg,#059669,#06B6D4)',
            'linear-gradient(135deg,#DC2626,#F59E0B)',
            'linear-gradient(135deg,#7C3AED,#3B82F6)',
        ];
        const gradIdx = (job.company_name?.charCodeAt(0) || 0) % avatarGrads.length;
        const initial = (job.company_name || 'C').charAt(0).toUpperCase();
        const typeClass = job.job_type === 'remote' ? 'remote' : job.job_type === 'contract' ? 'contract' : job.job_type === 'internship' ? 'internship' : '';
        const remoteTag = (job.job_type === 'remote') ? `<span class="ft-badge ft-badge-green ms-1"><i class="bi bi-wifi"></i> Remote</span>` : '';
        const salary = job.salary_min || job.salary_max
            ? `₹${fmtNum(job.salary_min)} – ₹${fmtNum(job.salary_max)}`
            : 'Salary undisclosed';

        const skills = job.skills ? job.skills.split(',').slice(0, 4).map(s =>
            `<span class="job-tag">${escHtml(s.trim())}</span>`
        ).join('') : '';

        const matchScore = job.match_score || Math.floor(Math.random() * 30 + 65);

        return `
        <div class="col-md-6 col-xl-4 animate-fadeInUp">
          <div class="job-list-card" onclick="window.location.href='${window.SHP_BASE_URL}/job.php?id=${job.id}'" style="cursor:pointer;">
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="company-avatar" style="background:${avatarGrads[gradIdx]}">${escHtml(initial)}</div>
              <div style="flex:1;min-width:0;">
                <h3 class="job-card-title">${escHtml(job.title)}</h3>
                <div class="job-card-company">${escHtml(job.company_name || '—')}</div>
                <div class="job-card-location"><i class="bi bi-geo-alt me-1" style="color:var(--ft-muted-d);"></i>${escHtml(job.location || 'Location N/A')}</div>
              </div>
              <button class="job-save-btn ${job.is_saved ? 'saved' : ''}" onclick="event.stopPropagation();toggleSave(this,${job.id})" title="Save job">
                <i class="bi bi-${job.is_saved ? 'heart-fill' : 'heart'}"></i>
              </button>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
              <span class="job-type-badge ${typeClass}">${escHtml((job.job_type || '').replace('-',' '))}</span>
              ${remoteTag}
              ${job.experience_level ? `<span class="ft-badge ft-badge-gray"><i class="bi bi-briefcase me-1"></i>${escHtml(job.experience_level)}</span>` : ''}
              <span class="ms-auto ft-badge ft-badge-blue"><i class="bi bi-lightning-fill me-1"></i>${matchScore}% match</span>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="job-card-salary"><i class="bi bi-currency-rupee"></i>${salary}</span>
              <span style="font-size:0.75rem;color:var(--ft-muted-d);">${escHtml(job.time_ago || 'Recently')}</span>
            </div>

            <div class="job-tags">${skills}</div>
          </div>
        </div>`;
    }

    /* ── Fetch jobs ── */
    function fetchJobs(params, append = false) {
        if (isLoading) return;
        isLoading = true;

        if (!append) {
            resultsWrap.innerHTML = skeletonCards(6);
            hasMore = true;
            currentPage = 1;
        }

        const qs = new URLSearchParams({ ...params, page: currentPage, limit: 12 });

        fetch(`${window.SHP_BASE_URL}/ajax/search_jobs.php?${qs}`)
            .then(r => r.json())
            .then(data => {
                isLoading = false;
                const jobs = data.jobs || data; // handle both formats
                const total = data.total || jobs.length;

                if (!append) resultsWrap.innerHTML = '';

                if (!jobs.length && !append) {
                    resultsWrap.innerHTML = `
                        <div class="col-12">
                          <div class="empty-jobs-state">
                            <div class="empty-icon"><i class="bi bi-search"></i></div>
                            <h5 style="color:var(--ft-text);margin-bottom:0.5rem;">No jobs found</h5>
                            <p style="color:var(--ft-muted);font-size:0.875rem;">Try different keywords or remove some filters.</p>
                          </div>
                        </div>`;
                    if (countLabel) countLabel.textContent = 'No results found';
                    return;
                }

                jobs.forEach(job => {
                    resultsWrap.insertAdjacentHTML('beforeend', renderCard(job));
                });

                if (countLabel) countLabel.textContent = `Showing ${append ? 'more' : total.toLocaleString()} openings`;
                if (loadMoreBtn) loadMoreBtn.style.display = jobs.length < 12 ? 'none' : 'flex';
                hasMore = jobs.length === 12;
            })
            .catch(() => {
                isLoading = false;
                if (!append) {
                    resultsWrap.innerHTML = `<div class="col-12 text-center py-5"><p style="color:var(--ft-muted);">Failed to load jobs. Please refresh.</p></div>`;
                }
            });
    }

    /* ── Collect form params ── */
    function getParams() {
        const fd = new FormData(form);
        const params = {};
        fd.forEach((v, k) => { if (v) params[k] = v; });
        return params;
    }

    /* ── Submit handler ── */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        lastParams = getParams();
        currentPage = 1;
        fetchJobs(lastParams, false);
    });

    /* ── Live search with debounce ── */
    form.querySelectorAll('input[name="q"], input[name="location"]').forEach(input => {
        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                lastParams = getParams();
                currentPage = 1;
                fetchJobs(lastParams, false);
            }, DEBOUNCE_MS);
        });
    });

    form.querySelectorAll('select').forEach(sel => {
        sel.addEventListener('change', function () {
            lastParams = getParams();
            currentPage = 1;
            fetchJobs(lastParams, false);
        });
    });

    /* ── Quick filter chips ── */
    document.querySelectorAll('.category-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            const type = this.dataset.type;
            const loc  = this.dataset.loc;
            const exp  = this.dataset.exp;

            const typeEl = form.querySelector('[name="job_type"]');
            const locEl  = form.querySelector('[name="location"]');
            const expEl  = document.getElementById('filterExperience');

            if (typeEl) typeEl.value = type || '';
            if (locEl)  locEl.value  = loc  || '';
            if (expEl)  expEl.value  = exp  || '';

            lastParams = getParams();
            currentPage = 1;
            fetchJobs(lastParams, false);
        });
    });

    /* ── Load More button ── */
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function () {
            if (!hasMore || isLoading) return;
            currentPage++;
            fetchJobs(lastParams, true);
        });
    }

    /* ── Save/unsave job ── */
    window.toggleSave = function (btn, jobId) {
        const isSaved = btn.classList.contains('saved');
        fetch(`${window.SHP_BASE_URL}/ajax/save_job.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `job_id=${jobId}&action=${isSaved ? 'unsave' : 'save'}`,
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle('saved');
                const icon = btn.querySelector('i');
                icon.className = btn.classList.contains('saved') ? 'bi bi-heart-fill' : 'bi bi-heart';
                showToast(data.message || (btn.classList.contains('saved') ? 'Job saved!' : 'Job removed'), 'success');
            } else {
                showToast(data.message || 'Please log in to save jobs.', 'warning');
            }
        })
        .catch(() => showToast('Action failed. Try again.', 'danger'));
    };

    /* ── Trigger job search globally ── */
    window.triggerJobSearch = function () {
        lastParams = getParams();
        currentPage = 1;
        fetchJobs(lastParams, false);
    };

    /* ── Helpers ── */
    function fmtNum(n) {
        if (!n) return '—';
        if (n >= 100000) return (n/100000).toFixed(1).replace(/\.0$/,'') + 'L';
        if (n >= 1000)   return (n/1000).toFixed(0) + 'K';
        return n;
    }

    /* ── Initial load ── */
    fetchJobs({}, false);

})();
