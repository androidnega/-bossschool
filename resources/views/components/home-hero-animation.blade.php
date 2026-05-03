{{-- Flat vector dashboard preview for SMS; CSS-only motion in app.css (.home-dash-*) --}}
<div class="home-dash-scene mx-auto w-full max-w-md rounded-2xl border border-stone-200/80 bg-white p-4 shadow-sm md:max-w-sm lg:max-w-md" aria-hidden="true">
    <svg class="home-dash-svg h-auto w-full" viewBox="0 0 360 300" fill="none" xmlns="http://www.w3.org/2000/svg" role="img">
        <title>{{ __('School dashboard preview') }}</title>

        {{-- Floating book + school mark --}}
        <g class="home-dash-float home-dash-float--slow">
            <rect x="248" y="12" width="44" height="36" rx="6" fill="#F8FAF7" stroke="#3E8A8E" stroke-width="1.25" stroke-opacity="0.45"/>
            <path d="M258 22h24M258 28h18M258 34h20" stroke="#7FA58C" stroke-width="2" stroke-linecap="round" opacity="0.85"/>
            <path d="M268 44 L280 20 L292 44 Z" fill="#E6EEC9" stroke="#3E8A8E" stroke-width="1.25" stroke-linejoin="round"/>
            <circle class="home-dash-pulse" cx="304" cy="28" r="4" fill="#3E8A8E" fill-opacity="0.55"/>
        </g>

        {{-- Row 1: Students | Fees --}}
        <g class="home-dash-float home-dash-float--a">
            <rect x="16" y="56" width="156" height="88" rx="12" fill="#FFFFFF" stroke="#3E8A8E" stroke-width="1.25" stroke-opacity="0.35"/>
            <circle cx="40" cy="84" r="12" fill="#E6EEC9" stroke="#7FA58C" stroke-width="1" stroke-opacity="0.5"/>
            <path d="M36 86l3 3 8-8" stroke="#3E8A8E" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="60" y="74" width="96" height="8" rx="3" fill="#3E8A8E" fill-opacity="0.2"/>
            <rect x="60" y="88" width="72" height="6" rx="2" fill="#78716c" fill-opacity="0.18"/>
            <rect x="60" y="112" width="88" height="6" rx="2" fill="#78716c" fill-opacity="0.12"/>
        </g>
        <g class="home-dash-float home-dash-float--b">
            <rect x="188" y="56" width="156" height="88" rx="12" fill="#FFFFFF" stroke="#7FA58C" stroke-width="1.25" stroke-opacity="0.4"/>
            <rect x="208" y="74" width="28" height="20" rx="4" fill="#F8FAF7" stroke="#3E8A8E" stroke-width="1" stroke-opacity="0.35"/>
            <rect x="212" y="78" width="20" height="4" rx="1" fill="#3E8A8E" fill-opacity="0.35"/>
            <rect x="244" y="74" width="84" height="8" rx="3" fill="#3E8A8E" fill-opacity="0.18"/>
            <rect x="244" y="88" width="64" height="6" rx="2" fill="#78716c" fill-opacity="0.16"/>
            <rect x="244" y="112" width="76" height="6" rx="2" fill="#78716c" fill-opacity="0.12"/>
        </g>

        {{-- Row 2: Results | Attendance --}}
        <g class="home-dash-float home-dash-float--c">
            <rect x="16" y="158" width="156" height="88" rx="12" fill="#FFFFFF" stroke="#B7C49A" stroke-width="1.25" stroke-opacity="0.55"/>
            <rect x="36" y="182" width="48" height="40" rx="4" fill="#F8FAF7" stroke="#3E8A8E" stroke-width="1" stroke-opacity="0.25"/>
            <path d="M48 210 L56 198 L64 206 L76 188" stroke="#3E8A8E" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" opacity="0.65"/>
            <rect x="96" y="178" width="60" height="8" rx="3" fill="#3E8A8E" fill-opacity="0.18"/>
            <rect x="96" y="192" width="48" height="6" rx="2" fill="#78716c" fill-opacity="0.14"/>
            <rect x="96" y="214" width="64" height="6" rx="2" fill="#78716c" fill-opacity="0.1"/>
        </g>
        <g class="home-dash-float home-dash-float--d">
            <rect x="188" y="158" width="156" height="88" rx="12" fill="#FFFFFF" stroke="#3E8A8E" stroke-width="1.25" stroke-opacity="0.3"/>
            <circle class="home-dash-pulse home-dash-pulse--delay" cx="220" cy="190" r="8" fill="#E6EEC9" stroke="#7FA58C" stroke-width="1" stroke-opacity="0.45"/>
            <path d="M216 190l2.5 2.5 6-6" stroke="#3E8A8E" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="240" y="178" width="84" height="8" rx="3" fill="#3E8A8E" fill-opacity="0.16"/>
            <rect x="240" y="192" width="68" height="6" rx="2" fill="#78716c" fill-opacity="0.14"/>
            <rect x="240" y="214" width="72" height="6" rx="2" fill="#78716c" fill-opacity="0.1"/>
        </g>

        {{-- Mini chart bars --}}
        <g class="home-dash-bars" transform="translate(100 262)">
            <rect class="home-dash-bar" x="0" y="-22" width="10" height="22" rx="2" fill="#3E8A8E" fill-opacity="0.55"/>
            <rect class="home-dash-bar" x="18" y="-34" width="10" height="34" rx="2" fill="#3E8A8E" fill-opacity="0.45"/>
            <rect class="home-dash-bar" x="36" y="-18" width="10" height="18" rx="2" fill="#7FA58C" fill-opacity="0.55"/>
            <rect class="home-dash-bar" x="54" y="-28" width="10" height="28" rx="2" fill="#3E8A8E" fill-opacity="0.5"/>
            <rect class="home-dash-bar" x="72" y="-14" width="10" height="14" rx="2" fill="#B7C49A" fill-opacity="0.65"/>
            <rect class="home-dash-bar" x="90" y="-26" width="10" height="26" rx="2" fill="#7FA58C" fill-opacity="0.45"/>
            <rect class="home-dash-bar" x="108" y="-20" width="10" height="20" rx="2" fill="#3E8A8E" fill-opacity="0.4"/>
            <rect class="home-dash-bar" x="126" y="-30" width="10" height="30" rx="2" fill="#3E8A8E" fill-opacity="0.5"/>
        </g>
    </svg>
</div>
