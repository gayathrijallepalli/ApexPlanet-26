/**
 * SmartHire Pro — Main JavaScript
 * Toast · Ripple · Sidebar · Counters · Notifications · OTP · Password Strength
 */
(function () {
    'use strict';

    /* ══════════════════════════════════════════
       TOAST SYSTEM
    ══════════════════════════════════════════ */
    const iconMap = {
        success: 'bi-check-circle-fill',
        danger:  'bi-x-circle-fill',
        error:   'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info:    'bi-info-circle-fill',
    };

    window.showToast = function (message, type = 'info', duration = 4000) {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const t = document.createElement('div');
        t.className = `ft-toast ft-toast-${type === 'error' ? 'danger' : type}`;
        t.innerHTML = `
            <i class="bi ${iconMap[type] || iconMap.info} ft-toast-icon"></i>
            <span class="ft-toast-msg">${message}</span>
            <button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;opacity:0.5;margin-left:auto;cursor:pointer;font-size:1rem;line-height:1;">×</button>
        `;
        container.appendChild(t);

        setTimeout(() => {
            t.style.animation = 'fadeIn 0.3s ease reverse';
            setTimeout(() => t.remove(), 300);
        }, duration);
    };

    /* ══════════════════════════════════════════
       RIPPLE EFFECT (all .btn)
    ══════════════════════════════════════════ */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn');
        if (!btn) return;
        const rect = btn.getBoundingClientRect();
        const ripple = document.createElement('span');
        const size = Math.max(rect.width, rect.height);
        ripple.style.cssText = `
            position:absolute;width:${size}px;height:${size}px;
            border-radius:50%;background:rgba(255,255,255,0.3);
            top:${e.clientY - rect.top - size/2}px;
            left:${e.clientX - rect.left - size/2}px;
            transform:scale(0);animation:ripple 0.5s ease;
            pointer-events:none;
        `;
        btn.style.position = 'relative';
        btn.style.overflow = 'hidden';
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
    });

    /* ══════════════════════════════════════════
       SIDEBAR MOBILE DRAWER
    ══════════════════════════════════════════ */
    window.openSidebar = function () {
        document.getElementById('dashSidebar')?.classList.add('show');
        document.getElementById('sidebarOverlay')?.classList.add('show');
        document.body.style.overflow = 'hidden';
    };
    window.closeSidebar = function () {
        document.getElementById('dashSidebar')?.classList.remove('show');
        document.getElementById('sidebarOverlay')?.classList.remove('show');
        document.body.style.overflow = '';
    };

    /* ══════════════════════════════════════════
       ANIMATED COUNTER (IntersectionObserver)
    ══════════════════════════════════════════ */
    function animateCounter(el) {
        const target = parseInt(el.dataset.target || el.textContent.replace(/\D/g, ''), 10);
        if (isNaN(target) || target === 0) return;
        const suffix = el.dataset.suffix || el.textContent.replace(/[\d,]/g, '').trim();
        const duration = 1800;
        const start = performance.now();
        const easeOut = t => 1 - Math.pow(1 - t, 3);

        function tick(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const current = Math.round(easeOut(progress) * target);
            el.textContent = current.toLocaleString() + suffix;
            if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    function initCounters() {
        const counters = document.querySelectorAll('[data-counter]');
        if (!counters.length) return;
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        counters.forEach(el => observer.observe(el));
    }

    /* ══════════════════════════════════════════
       SCROLL ANIMATIONS
    ══════════════════════════════════════════ */
    function initScrollAnimations() {
        const els = document.querySelectorAll('[data-animate]');
        if (!els.length) return;
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const anim = entry.target.dataset.animate;
                    entry.target.style.animation = `${anim} 0.6s ease both`;
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        els.forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });
    }

    /* ══════════════════════════════════════════
       NOTIFICATION DROPDOWN LOADER
    ══════════════════════════════════════════ */
    const notifToggle = document.getElementById('notifToggle');
    let notifLoaded = false;

    if (notifToggle) {
        notifToggle.addEventListener('click', function () {
            if (notifLoaded) return;
            notifLoaded = true;
            fetch(window.SHP_BASE_URL + '/ajax/get_notifications.php')
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('notifList');
                    if (!list) return;
                    if (!data.length) {
                        list.innerHTML = '<div style="text-align:center;padding:1.5rem 1rem;color:var(--ft-muted);"><i class="bi bi-bell-slash" style="font-size:2rem;opacity:0.4;"></i><p style="margin-top:0.75rem;font-size:0.875rem;">No notifications yet</p></div>';
                        return;
                    }
                    list.innerHTML = data.map(n => `
                        <div class="notif-item ${n.is_read == '0' ? 'unread' : ''}" onclick="markNotifRead(${n.id}, '${n.link || '#'}')">
                            <div class="notif-title">${escHtml(n.title)}</div>
                            <div class="notif-body">${escHtml(n.message)}</div>
                            <div class="notif-time">${escHtml(n.time_ago || '')}</div>
                        </div>
                    `).join('');
                })
                .catch(() => {
                    const list = document.getElementById('notifList');
                    if (list) list.innerHTML = '<p style="color:var(--ft-danger);font-size:0.875rem;text-align:center;">Failed to load notifications</p>';
                });
        });
    }

    window.markNotifRead = function (id, link) {
        fetch(window.SHP_BASE_URL + '/ajax/mark_notification_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id,
        }).then(() => {
            const badge = document.getElementById('notifBadge');
            if (badge) {
                const count = parseInt(badge.textContent) - 1;
                if (count <= 0) badge.remove();
                else badge.textContent = count;
            }
            if (link && link !== '#') window.location.href = link;
        });
    };

    window.markAllRead = function () {
        fetch(window.SHP_BASE_URL + '/ajax/mark_notification_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'all=1',
        }).then(() => {
            document.getElementById('notifBadge')?.remove();
            document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
        });
    };

    /* ══════════════════════════════════════════
       OTP 6-BOX INPUT
    ══════════════════════════════════════════ */
    function initOtpInputs() {
        const boxes = document.querySelectorAll('.otp-box');
        if (!boxes.length) return;
        boxes.forEach((box, i) => {
            box.addEventListener('input', () => {
                const val = box.value.replace(/\D/g, '');
                box.value = val.slice(-1);
                if (val && boxes[i + 1]) boxes[i + 1].focus();
                updateOtpHidden();
            });
            box.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !box.value && boxes[i - 1]) {
                    boxes[i - 1].focus();
                }
                if (e.key === 'ArrowLeft' && boxes[i - 1]) boxes[i - 1].focus();
                if (e.key === 'ArrowRight' && boxes[i + 1]) boxes[i + 1].focus();
            });
            box.addEventListener('paste', e => {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                [...text.slice(0, 6)].forEach((ch, j) => {
                    if (boxes[j]) boxes[j].value = ch;
                });
                if (boxes[Math.min(text.length, 5)]) boxes[Math.min(text.length, 5)].focus();
                updateOtpHidden();
            });
        });
    }
    function updateOtpHidden() {
        const hidden = document.getElementById('otpHidden');
        if (!hidden) return;
        hidden.value = [...document.querySelectorAll('.otp-box')].map(b => b.value).join('');
    }

    /* ══════════════════════════════════════════
       PASSWORD STRENGTH METER
    ══════════════════════════════════════════ */
    function initPasswordStrength() {
        const pwd = document.getElementById('passwordInput');
        const bar = document.getElementById('pwdStrengthBar');
        const label = document.getElementById('pwdStrengthLabel');
        if (!pwd || !bar) return;

        const levels = [
            { min: 0,  color: '#EF4444', text: 'Too weak',  w: '20%' },
            { min: 25, color: '#F59E0B', text: 'Weak',      w: '40%' },
            { min: 50, color: '#F59E0B', text: 'Fair',      w: '60%' },
            { min: 75, color: '#22C55E', text: 'Strong',    w: '80%' },
            { min: 90, color: '#22C55E', text: 'Very strong', w: '100%' },
        ];

        pwd.addEventListener('input', () => {
            const v = pwd.value;
            let score = 0;
            if (v.length >= 8)            score += 25;
            if (/[A-Z]/.test(v))          score += 25;
            if (/[0-9]/.test(v))          score += 25;
            if (/[^A-Za-z0-9]/.test(v))  score += 25;

            const level = [...levels].reverse().find(l => score >= l.min) || levels[0];
            bar.style.width = v ? level.w : '0';
            bar.style.background = level.color;
            if (label) label.textContent = v ? level.text : '';
        });
    }

    /* ══════════════════════════════════════════
       FLASH → TOAST
    ══════════════════════════════════════════ */
    function flashToToast() {
        const alerts = document.querySelectorAll('.alert[data-auto-toast]');
        alerts.forEach(el => {
            const type = [...el.classList].find(c => c.startsWith('alert-'))?.replace('alert-','') || 'info';
            showToast(el.textContent.trim(), type);
            el.remove();
        });
    }

    /* ══════════════════════════════════════════
       HELPERS
    ══════════════════════════════════════════ */
    window.escHtml = function (str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    };

    /* ══════════════════════════════════════════
       SKELETON → CONTENT
    ══════════════════════════════════════════ */
    window.removeSkeleton = function (containerId) {
        const el = document.getElementById(containerId);
        if (el) el.querySelectorAll('.skeleton').forEach(s => s.remove());
    };

    /* ══════════════════════════════════════════
       INIT
    ══════════════════════════════════════════ */
    document.addEventListener('DOMContentLoaded', function () {
        initCounters();
        initScrollAnimations();
        initOtpInputs();
        initPasswordStrength();
        flashToToast();

        // Topbar search shortcut
        const topSearch = document.getElementById('topbarSearch');
        if (topSearch) {
            topSearch.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    window.location.href = window.SHP_BASE_URL + '/index.php?q=' + encodeURIComponent(topSearch.value);
                }
            });
        }
    });

})();
