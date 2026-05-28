<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')BossSchool | @yield('title')@else BossSchool @endif</title>
    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @endif
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @include('layouts.partials.head-assets')
    @yield('head_extra')
    @stack('head')
</head>
<body class="@yield('body_class', 'min-h-screen bg-page-soft font-sans text-gray-900 antialiased')">
    @yield('content')
    @stack('scripts')
</body>
</html>
