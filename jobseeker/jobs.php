<?php
require_once __DIR__ . '/../includes/init.php';
requireRole(['jobseeker']);

$userId = (int) $_SESSION['user_id'];

$pageTitle = 'Explore Opportunities';
$extraJs = [asset('js/job-search.js')];
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/dashboard-layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="page-heading">Search Opportunities</h1>
        <p class="page-subtitle">Find roles that match your technical skill set and matching criteria.</p>
    </div>
</div>

<!-- ── STICKY GLASS SEARCH PANEL ── -->
<div class="glass-card p-4 mb-4 sticky-top" style="top: 70px; z-index: 100; backdrop-filter: var(--glass-blur);">
    <form id="jobSearchForm">
        <div class="row g-2 g-md-3 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label text-muted small mb-1">Keywords</label>
                <div class="position-relative">
                    <input type="text" name="q" class="form-control ps-4" placeholder="Job title, technical skill, role...">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;"></i>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label text-muted small mb-1">Location</label>
                <div class="position-relative">
                    <input type="text" name="location" class="form-control ps-4" placeholder="City or 'Remote'">
                    <i class="bi bi-geo-alt position-absolute top-50 translate-middle-y text-muted" style="left:12px;"></i>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label text-muted small mb-1">Candidacy Type</label>
                <select name="job_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="full-time">Full-Time</option>
                    <option value="part-time">Part-Time</option>
                    <option value="contract">Contract</option>
                    <option value="internship">Internship</option>
                    <option value="remote">Remote</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label text-muted small mb-1">Exp Level</label>
                <select name="experience" class="form-select">
                    <option value="">All Levels</option>
                    <option value="Entry">Entry (0-2 yr)</option>
                    <option value="Mid">Mid (2-5 yr)</option>
                    <option value="Senior">Senior (5+ yr)</option>
                </select>
            </div>
            <div class="col-lg-1 col-md-4">
                <button type="submit" class="btn btn-primary w-100 hover-lift d-flex align-items-center justify-content-center" style="height:40px;"><i class="bi bi-sliders fs-5"></i></button>
            </div>
        </div>
        <input type="hidden" name="salary" id="filterSalary" value="">
    </form>
</div>

<!-- ── TWO-COLUMN SPLIT CONTAINER ── -->
<div class="row g-4">
    <!-- Filter Sidebar (Col 3) -->
    <div class="col-lg-3">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-white"><i class="bi bi-funnel me-2 text-primary"></i>Filters</h6>
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" id="clearFiltersBtn">Clear All</button>
            </div>
            
            <hr class="border-white border-opacity-10 my-3">
            
            <div class="mb-4">
                <label class="form-label text-white small fw-bold mb-2">Compensation Range</label>
                <div class="d-flex align-items-center justify-content-between text-muted small mb-2">
                    <span>Min CTC</span>
                    <span id="salaryDisplay">₹0</span>
                </div>
                <input type="range" class="form-range" id="salaryRange" min="0" max="3000000" step="100000" value="0">
                <div class="d-flex justify-content-between text-muted" style="font-size:0.7rem;">
                    <span>₹0</span>
                    <span>₹30L+</span>
                </div>
            </div>

            <div>
                <label class="form-label text-white small fw-bold mb-2">Popular Skills</label>
                <div class="d-flex flex-wrap gap-2" id="filterSkillsChips">
                    <button class="badge bg-white bg-opacity-5 text-muted py-2 px-3 border border-white border-opacity-5 rounded-pill btn-chip" data-skill="React">React</button>
                    <button class="badge bg-white bg-opacity-5 text-muted py-2 px-3 border border-white border-opacity-5 rounded-pill btn-chip" data-skill="Node.js">Node.js</button>
                    <button class="badge bg-white bg-opacity-5 text-muted py-2 px-3 border border-white border-opacity-5 rounded-pill btn-chip" data-skill="PHP">PHP</button>
                    <button class="badge bg-white bg-opacity-5 text-muted py-2 px-3 border border-white border-opacity-5 rounded-pill btn-chip" data-skill="Python">Python</button>
                    <button class="badge bg-white bg-opacity-5 text-muted py-2 px-3 border border-white border-opacity-5 rounded-pill btn-chip" data-skill="SQL">SQL</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Listing (Col 9) -->
    <div class="col-lg-9">
        <div id="jobResults" class="row g-4">
            <!-- Skeletons load here dynamically from JS -->
            <div class="col-12 text-center text-muted py-5">
                <div class="spinner-border spinner-border-sm me-2 text-primary"></div>Processing openings...
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const salaryRange = document.getElementById('salaryRange');
    const salaryDisplay = document.getElementById('salaryDisplay');
    const filterSalary = document.getElementById('filterSalary');
    const searchForm = document.getElementById('jobSearchForm');

    salaryRange.addEventListener('input', (e) => {
        const val = parseInt(e.target.value);
        salaryDisplay.textContent = val === 0 ? '₹0' : '₹' + (val / 100000).toFixed(1) + 'L';
        filterSalary.value = val > 0 ? val : '';
        // Trigger search debounced
        if (window.triggerJobSearch) {
            window.triggerJobSearch();
        }
    });

    const clearBtn = document.getElementById('clearFiltersBtn');
    clearBtn.addEventListener('click', () => {
        searchForm.reset();
        salaryRange.value = 0;
        salaryDisplay.textContent = '₹0';
        filterSalary.value = '';
        document.querySelectorAll('#filterSkillsChips .btn-chip').forEach(c => {
            c.classList.remove('bg-primary', 'text-white');
            c.classList.add('bg-white', 'bg-opacity-5', 'text-muted');
        });
        if (window.triggerJobSearch) {
            window.triggerJobSearch();
        }
    });

    // Handle quick skill chips click
    document.querySelectorAll('#filterSkillsChips .btn-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            const queryInput = searchForm.querySelector('input[name="q"]');
            const skill = chip.getAttribute('data-skill');
            
            if (chip.classList.contains('bg-primary')) {
                chip.classList.remove('bg-primary', 'text-white');
                chip.classList.add('bg-white', 'bg-opacity-5', 'text-muted');
                queryInput.value = queryInput.value.replace(skill, '').trim();
            } else {
                chip.classList.add('bg-primary', 'text-white');
                chip.classList.remove('bg-white', 'bg-opacity-5', 'text-muted');
                queryInput.value = (queryInput.value + ' ' + skill).trim();
            }
            if (window.triggerJobSearch) {
                window.triggerJobSearch();
            }
        });
    });
});
</script>

<?php
require_once BASE_PATH . '/includes/dashboard-end.php';
require_once BASE_PATH . '/includes/footer.php';
?>
