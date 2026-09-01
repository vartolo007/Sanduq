{{-- Authenticated shell: sidebar + header on desktop, app bar + bottom nav on mobile. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ?? __('app.brand') }} — {{ __('app.brand') }}</title>
    <link rel="stylesheet" href="{{ asset('css/industry.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanduq.css') }}">
    <script>
        // Applied before first paint so the dark theme never flashes light.
        try {
            var t = localStorage.getItem('sq-theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
        } catch (e) {}
    </script>
</head>
<body>
<div class="sq-shell">
    <x-sidebar />

    <div class="sq-main">
        <x-header :title="$title ?? ''" />
        <x-mobile-appbar :title="$title ?? ''" />

        <main class="sq-page">
            {{ $slot }}
        </main>

        <x-bottom-nav />

        <a href="{{ route('transactions.create') }}" class="sq-fab blueprint sq-tap" aria-label="{{ __('app.add_tx') }}">
            <x-corners />
            <x-icon name="plus" size="26" />
        </a>
    </div>
</div>

<x-confirm-dialog />
@if (session('status'))
    <x-toast :message="session('status')" />
@endif

<script src="{{ asset('js/sanduq.js') }}" defer></script>
</body>
</html>
