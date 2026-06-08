/**
 * SmartHire Pro — Dark Mode Toggle
 * Defaults to dark, persists in localStorage
 */
(function () {
    'use strict';

    const STORAGE_KEY = 'shp_theme';
    const DEFAULT = 'dark';

    function getTheme()  { return localStorage.getItem(STORAGE_KEY) || DEFAULT; }
    function setTheme(t) {
        localStorage.setItem(STORAGE_KEY, t);
        document.documentElement.setAttribute('data-theme', t);
        updateIcon(t);
    }
    function updateIcon(t) {
        document.querySelectorAll('#themeIcon').forEach(icon => {
            icon.className = t === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
        });
    }

    // Apply immediately on load (also done inline in <head>)
    setTheme(getTheme());

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#darkModeToggle').forEach(btn => {
            btn.addEventListener('click', function () {
                const current = getTheme();
                setTheme(current === 'dark' ? 'light' : 'dark');
            });
        });
    });
})();
