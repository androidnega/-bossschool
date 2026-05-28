{{--
    Inline OTP-boxes initialiser.

    Shipped with the page HTML on purpose so it can never be served stale
    from a CDN/disk cache, and so it works regardless of <script defer>
    timing. Wires up any form on the page that follows the contract:

        <form data-otp-form action="...">
            <input type="hidden" name="code" data-otp-value>
            <input data-otp-digit ...> × 6
        </form>

    Behaviour:
      - Each box accepts exactly one digit; focus auto-advances.
      - Backspace on an empty box jumps back and clears the previous one.
      - Left / Right arrows move between boxes.
      - Pasting a 6-digit string fills every box at once.
      - The hidden input is kept in sync so the server still receives
        `code` as a single string.
      - The form submits automatically once all 6 boxes are filled.
--}}
<script>
(function () {
    'use strict';

    function initOtpForm(form) {
        var digits = Array.prototype.slice.call(form.querySelectorAll('[data-otp-digit]'));
        var hidden = form.querySelector('[data-otp-value]');
        if (!digits.length || !hidden) return;

        function sync() {
            hidden.value = digits.map(function (d) { return d.value; }).join('');
        }
        function isComplete() {
            return digits.every(function (d) { return /^\d$/.test(d.value); });
        }
        function maybeSubmit() {
            if (!isComplete()) return;
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        }

        digits.forEach(function (input, i) {
            input.addEventListener('input', function () {
                var v = (input.value || '').replace(/\D/g, '');
                input.value = v.slice(0, 1);

                if (input.value && i < digits.length - 1) {
                    var next = digits[i + 1];
                    next.focus();
                    if (typeof next.select === 'function') next.select();
                }
                sync();
                maybeSubmit();
            });

            input.addEventListener('keydown', function (event) {
                if (event.key === 'Backspace' && !input.value && i > 0) {
                    event.preventDefault();
                    digits[i - 1].focus();
                    digits[i - 1].value = '';
                    sync();
                } else if (event.key === 'ArrowLeft' && i > 0) {
                    event.preventDefault();
                    digits[i - 1].focus();
                } else if (event.key === 'ArrowRight' && i < digits.length - 1) {
                    event.preventDefault();
                    digits[i + 1].focus();
                }
            });

            input.addEventListener('paste', function (event) {
                var data = event.clipboardData || window.clipboardData;
                if (!data) return;
                var text = String(data.getData('text') || '').replace(/\D/g, '');
                if (!text) return;
                event.preventDefault();
                digits.forEach(function (d, idx) { d.value = text.charAt(idx) || ''; });
                var nextIdx = Math.min(text.length, digits.length - 1);
                digits[nextIdx].focus();
                sync();
                maybeSubmit();
            });

            input.addEventListener('focus', function () {
                if (typeof input.select === 'function') input.select();
            });
        });

        sync();
    }

    function initAll() {
        document.querySelectorAll('form[data-otp-form]').forEach(initOtpForm);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
</script>
