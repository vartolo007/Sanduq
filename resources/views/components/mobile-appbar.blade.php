@props(['title' => ''])
<header class="sq-topbar sq-only-mobile">
    <span class="sq-mark" style="width:30px;height:30px;font-size:16px;">ص</span>
    <h1>{{ $title }}</h1>
    <x-lang-switch :icon-only="true" />
    <button type="button" class="btn btn-secondary btn-icon sq-tap" onclick="sqToggleTheme()"
            aria-label="{{ __('app.theme') }}" style="width:34px;height:34px;">
        <x-icon name="moon" size="18" />
    </button>
</header>
