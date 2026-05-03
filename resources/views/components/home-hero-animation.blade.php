{{-- Vector hero: school + dashboard motif; animations via app.css --}}
<div class="home-anim-root mx-auto flex max-w-lg items-center justify-center px-2 py-4" aria-hidden="true">
    <svg class="home-anim-svg h-auto w-full max-w-[min(100%,28rem)] text-primary" viewBox="0 0 440 360" fill="none" xmlns="http://www.w3.org/2000/svg" role="img">
        <title>{{ __('School management illustration') }}</title>
        <defs>
            <linearGradient id="home-anim-grad-screen" x1="120" y1="60" x2="400" y2="320" gradientUnits="userSpaceOnUse">
                <stop stop-color="#3E8A8E" stop-opacity="0.12"/>
                <stop offset="1" stop-color="#7FA58C" stop-opacity="0.08"/>
            </linearGradient>
            <linearGradient id="home-anim-grad-bar" x1="0" y1="1" x2="0" y2="0" gradientUnits="objectBoundingBox">
                <stop stop-color="#3E8A8E"/>
                <stop offset="1" stop-color="#5a9ea1"/>
            </linearGradient>
        </defs>

        {{-- Soft backdrop orbs --}}
        <circle class="home-anim-orb home-anim-orb-a" cx="72" cy="100" r="48" fill="#3E8A8E" fill-opacity="0.06"/>
        <circle class="home-anim-orb home-anim-orb-b" cx="360" cy="240" r="64" fill="#7FA58C" fill-opacity="0.07"/>

        {{-- Floating “screen” panel --}}
        <g class="home-anim-panel">
            <rect x="118" y="78" width="264" height="196" rx="18" fill="url(#home-anim-grad-screen)" stroke="#3E8A8E" stroke-opacity="0.22" stroke-width="1.5"/>
            <rect x="138" y="98" width="224" height="12" rx="4" fill="#3E8A8E" fill-opacity="0.15"/>
            <rect x="138" y="118" width="120" height="8" rx="3" fill="#78716c" fill-opacity="0.2"/>

            {{-- Animated bar chart --}}
            <g class="home-anim-bars" transform="translate(158 248)">
                <rect class="home-anim-bar home-anim-bar-1" x="0" y="-72" width="28" height="72" rx="4" fill="url(#home-anim-grad-bar)" fill-opacity="0.85"/>
                <rect class="home-anim-bar home-anim-bar-2" x="52" y="-104" width="28" height="104" rx="4" fill="url(#home-anim-grad-bar)" fill-opacity="0.75"/>
                <rect class="home-anim-bar home-anim-bar-3" x="104" y="-56" width="28" height="56" rx="4" fill="url(#home-anim-grad-bar)" fill-opacity="0.9"/>
                <rect class="home-anim-bar home-anim-bar-4" x="156" y="-88" width="28" height="88" rx="4" fill="#7FA58C" fill-opacity="0.8"/>
            </g>
            <line x1="148" y1="248" x2="352" y2="248" stroke="#78716c" stroke-opacity="0.25" stroke-width="1" stroke-linecap="round"/>

            {{-- Checklist ticks --}}
            <g class="home-anim-checks" transform="translate(148 150)">
                <circle cx="10" cy="10" r="10" fill="#3E8A8E" fill-opacity="0.12"/>
                <path class="home-anim-tick home-anim-tick-1" d="M6 10l2.5 2.5L14 7" stroke="#3E8A8E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="10" cy="36" r="10" fill="#3E8A8E" fill-opacity="0.12"/>
                <path class="home-anim-tick home-anim-tick-2" d="M6 36l2.5 2.5L14 33" stroke="#3E8A8E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="10" cy="62" r="10" fill="#3E8A8E" fill-opacity="0.12"/>
                <path class="home-anim-tick home-anim-tick-3" d="M6 62l2.5 2.5L14 59" stroke="#3E8A8E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </g>
        </g>

        {{-- School / building mark --}}
        <g class="home-anim-school">
            <path d="M52 248 L52 168 L108 132 L164 168 L164 248 Z" fill="#fff" stroke="#3E8A8E" stroke-width="2" stroke-linejoin="round"/>
            <path d="M88 248 L88 198 L128 198 L128 248" fill="#3E8A8E" fill-opacity="0.2"/>
            <path d="M108 132 L108 118" stroke="#3E8A8E" stroke-width="2" stroke-linecap="round"/>
            <circle cx="108" cy="108" r="10" fill="#B7C49A" fill-opacity="0.9"/>
            <rect x="98" y="218" width="20" height="30" rx="2" fill="#3E8A8E" fill-opacity="0.35"/>
        </g>

        {{-- Graduation cap + signal (SMS / connectivity) --}}
        <g class="home-anim-cap">
            <ellipse cx="332" cy="118" rx="36" ry="10" fill="#3E8A8E" fill-opacity="0.9"/>
            <path d="M296 118 L332 98 L368 118 L332 138 Z" fill="#2d6d70"/>
            <path class="home-anim-wave home-anim-wave-1" d="M378 108 Q392 98 406 108" stroke="#7FA58C" stroke-width="2.5" fill="none" stroke-linecap="round" opacity="0.7"/>
            <path class="home-anim-wave home-anim-wave-2" d="M378 122 Q396 112 414 122" stroke="#7FA58C" stroke-width="2" fill="none" stroke-linecap="round" opacity="0.5"/>
            <path class="home-anim-wave home-anim-wave-3" d="M378 136 Q398 128 418 136" stroke="#7FA58C" stroke-width="1.5" fill="none" stroke-linecap="round" opacity="0.35"/>
        </g>
    </svg>
</div>
