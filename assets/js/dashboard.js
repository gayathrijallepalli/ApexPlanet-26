/**
 * SmartHire Pro — Dashboard Interactions
 * Circular Gauges · Interview Scheduler · Pipeline Status AJAX Updates
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initCircularGauges();
        initInterviewScheduler();
        initApplicationActions();
    });

    /**
     * Animate SVG circular progress rings (e.g. Match Score, Profile Strength)
     */
    function initCircularGauges() {
        const rings = document.querySelectorAll('.progress-ring__circle');
        rings.forEach(circle => {
            const percent = parseFloat(circle.getAttribute('data-percent')) || 0;
            const radius = circle.r.baseVal.value;
            const circumference = radius * 2 * Math.PI;

            circle.style.strokeDasharray = `${circumference} ${circumference}`;
            circle.style.strokeDashoffset = circumference;

            // Trigger reflow & animate
            setTimeout(() => {
                const offset = circumference - (percent / 100) * circumference;
                circle.style.transition = 'stroke-dashoffset 1.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
                circle.style.strokeDashoffset = offset;
            }, 150);
        });
    }

    /**
     * Handle schedule interview form submit via AJAX
     */
    function initInterviewScheduler() {
        const form = document.getElementById('scheduleInterviewForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Scheduling...';

            const formData = new FormData(form);

            fetch(window.SHP_BASE_URL + '/ajax/schedule_interview.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (window.showToast) {
                        window.showToast(data.message, 'success');
                    } else {
                        alert(data.message);
                    }

                    // Hide modal
                    const modalEl = document.getElementById('interviewModal');
                    if (modalEl) {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }

                    // Reload page or dynamically move card to scheduled
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    if (window.showToast) {
                        window.showToast(data.message, 'danger');
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showToast) {
                    window.showToast('Failed to schedule interview.', 'danger');
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            });
        });

        // Set application ID when opening interview scheduler modal
        const modalEl = document.getElementById('interviewModal');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', function (e) {
                const trigger = e.relatedTarget;
                if (trigger) {
                    const appId = trigger.getAttribute('data-application-id');
                    const input = modalEl.querySelector('input[name="application_id"]');
                    if (input && appId) {
                        input.value = appId;
                    }
                }
            });
        }
    }

    /**
     * Handle quick status change button clicks in Recruiter/Jobseeker panel
     */
    function initApplicationActions() {
        const statusButtons = document.querySelectorAll('[data-action="update-status"]');
        statusButtons.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const appId = btn.getAttribute('data-application-id');
                const nextStatus = btn.getAttribute('data-status');
                const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';

                if (!appId || !nextStatus) return;

                btn.disabled = true;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                const formData = new FormData();
                formData.append('application_id', appId);
                formData.append('status', nextStatus);
                formData.append('csrf_token', csrfToken);

                fetch(window.SHP_BASE_URL + '/ajax/update_application_status.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (window.showToast) {
                            window.showToast(data.message || 'Status updated!', 'success');
                        }
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        if (window.showToast) {
                            window.showToast(data.message || 'Failed to update status.', 'danger');
                        }
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (window.showToast) {
                        window.showToast('Network error updating status.', 'danger');
                    }
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
            });
        });
    }

})();
