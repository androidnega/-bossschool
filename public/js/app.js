/* BossSchool — plain-JS helpers loaded on every page.
 *
 * Kept tiny on purpose. No build step required.
 */

(function () {
    'use strict';

    // Close any <details data-dropdown> when clicking outside.
    document.addEventListener('click', function (event) {
        document.querySelectorAll('details[data-dropdown][open]').forEach(function (details) {
            if (!details.contains(event.target)) {
                details.removeAttribute('open');
            }
        });
    });

    // Close any open dropdown on Escape, and return focus to its <summary>.
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('details[data-dropdown][open]').forEach(function (details) {
            details.removeAttribute('open');
            var summary = details.querySelector('summary');
            if (summary instanceof HTMLElement) {
                summary.focus();
            }
        });
    });

    // Note: the 2FA "OTP boxes" initialiser is shipped inline on the
    // two-factor views via resources/views/two_factor/partials/otp-boxes-script.blade.php
    // so it can never be served stale from a CDN/disk cache. Keep this file
    // for any cross-page helpers (dropdowns, etc.).
})();
