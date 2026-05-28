{{--
    Shared <head> assets — Tailwind via Play CDN.

    This file is the single source of truth for how every BossSchool page loads
    Tailwind, the project theme, fonts, icons and the small JS helpers.

    Loaded by:
      - layouts/app.blade.php
      - layouts/guest.blade.php
      - layouts/marketing.blade.php
      - errors/layout.blade.php
      - maintenance.blade.php
      - students/report-card.blade.php
--}}

{{-- Inter (used by .font-sans via the Tailwind config below) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">

{{-- Font Awesome (icon set used across the app) --}}
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    crossorigin="anonymous"
    referrerpolicy="no-referrer">

{{-- Tailwind Play CDN --}}
<script src="https://cdn.tailwindcss.com"></script>

{{-- Project theme — keep in sync with the (now-unused) tailwind.config.js --}}
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary:    '#3E8A8E',
                    secondary:  '#7FA58C',
                    accent:     '#B7C49A',
                    soft:       '#E6EEC9',
                    page:       '#FFFFFF',
                    'page-soft':'#F8FAF7',
                    canvas:     '#f0f0f0',
                    'card-mist':  '#f4f1ec',
                    'card-sage':  '#ecf0e8',
                    'card-sand':  '#f3efe8',
                    'card-shell': '#efeae4',
                },
                fontFamily: {
                    sans: [
                        'Inter',
                        'ui-sans-serif',
                        'system-ui',
                        'Segoe UI',
                        'Roboto',
                        'Helvetica Neue',
                        'Arial',
                        'Noto Sans',
                        'sans-serif',
                        'Apple Color Emoji',
                        'Segoe UI Emoji',
                        'Segoe UI Symbol',
                        'Noto Color Emoji',
                    ],
                },
                borderRadius: {
                    '4xl': '2rem',
                    '5xl': '2.5rem',
                },
                boxShadow: {
                    soft: '0 24px 70px rgba(15, 23, 42, 0.10)',
                    card: '0 14px 40px rgba(15, 23, 42, 0.07)',
                    tiny: '0 8px 24px rgba(15, 23, 42, 0.06)',
                },
            },
        },
    };
</script>

{{-- Custom component classes (.home-*, .dash-card-*, .glass-*, .login-card-pull-in).
     Cache-busted by the file's mtime so any edit to public/css/app.css is
     picked up by browsers without manual hard-reload. --}}
@php
    $cssPath = public_path('css/app.css');
    $cssVer  = is_file($cssPath) ? filemtime($cssPath) : null;
    $jsPath  = public_path('js/app.js');
    $jsVer   = is_file($jsPath) ? filemtime($jsPath) : null;
@endphp
<link rel="stylesheet" href="{{ asset('css/app.css') }}{{ $cssVer ? '?v='.$cssVer : '' }}">

{{-- Tiny JS helpers (dropdowns, OTP boxes, etc.) --}}
<script defer src="{{ asset('js/app.js') }}{{ $jsVer ? '?v='.$jsVer : '' }}"></script>
