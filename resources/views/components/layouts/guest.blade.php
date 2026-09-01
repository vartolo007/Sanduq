{{-- Login / register shell. Same identity, no navigation. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ?? __('app.brand') }}</title>
    <link rel="stylesheet" href="{{ asset('css/industry.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanduq.css') }}">
    <script>
        try {
            var t = localStorage.getItem('sq-theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
        } catch (e) {}
    </script>
</head>
<body>
<div style="min-height:100vh;display:grid;place-items:center;padding:24px;">
    <div style="width:min(430px,100%);display:flex;flex-direction:column;gap:22px;">
        <div class="sq-row">
            <span class="sq-mark" style="width:42px;height:42px;font-size:22px;">ص</span>
            <div>
                <div style="font-family:var(--font-heading);font-size:24px;line-height:1;">{{ __('app.brand') }}</div>
                <div class="sq-label">{{ __('app.brand_sub') }}</div>
            </div>
        </div>

        {{ $slot }}

        <div class="sq-row" style="justify-content:center;">
            <x-lang-switch />
            <button type="button" class="btn btn-ghost sq-tap" onclick="sqToggleTheme()">
                <x-icon name="moon" /> {{ __('app.theme') }}
            </button>
        </div>
    </div>
</div>
<script src="{{ asset('js/sanduq.js') }}" defer></script>
</body>
</html>
