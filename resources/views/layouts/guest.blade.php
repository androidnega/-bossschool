<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @include('layouts.partials.head-assets')
</head>
<body class="min-h-screen bg-page font-sans text-gray-900 antialiased">
    <main class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        @yield('content')
    </main>

    {{-- Password eye toggle. Any <button data-pw-toggle="<input-id>"> on
         the page becomes a show/hide control for the matching password
         input. Cosmetic only — the form still submits the real value. --}}
    <script>
        (function () {
            document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
                var targetId = btn.getAttribute('data-pw-toggle');
                var input = document.getElementById(targetId);
                if (! input) return;
                var icon = btn.querySelector('[data-pw-icon]');
                btn.addEventListener('click', function () {
                    var revealed = input.getAttribute('type') === 'text';
                    input.setAttribute('type', revealed ? 'password' : 'text');
                    btn.setAttribute('aria-pressed', String(! revealed));
                    btn.setAttribute('aria-label', revealed ? 'Show password' : 'Hide password');
                    if (icon) {
                        icon.classList.toggle('fa-eye', revealed);
                        icon.classList.toggle('fa-eye-slash', ! revealed);
                    }
                    input.focus();
                });
            });
        })();
    </script>
</body>
</html>
