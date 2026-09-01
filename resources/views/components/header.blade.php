@props(['title' => ''])
<header class="sq-header">
    <div style="min-width:0;">
        <div class="sq-label" style="font-size:10px;">{{ __('app.brand') }} / {{ $title }}</div>
        <h1 style="margin:0;font-family:var(--font-heading);font-size:22px;">{{ $title }}</h1>
    </div>
    <div style="flex:1;"></div>

    <form method="GET" action="{{ route('transactions.index') }}" class="sq-search" style="width:min(280px,26vw);">
        <span class="sq-mute" style="display:grid;flex:none;"><x-icon name="search" size="17" /></span>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('app.search_ph') }}"
               aria-label="{{ __('app.search_ph') }}">
    </form>

    <x-lang-switch :icon-only="true" />
    <button type="button" class="btn btn-secondary btn-icon sq-tap" onclick="sqToggleTheme()"
            title="{{ __('app.theme') }}" aria-label="{{ __('app.theme') }}">
        <x-icon name="moon" size="18" />
    </button>

    <div class="sq-row" style="padding-inline-start:8px;border-inline-start:1px solid var(--color-divider);">
        <span class="sq-icon-box" style="font-family:var(--font-heading);font-size:14px;">{{ auth()->user()->initials() }}</span>
        <span style="line-height:1.2;">
            <span style="display:block;font-size:13px;">{{ auth()->user()->name ?? __('app.user_name') }}</span>
            <span class="sq-mute" style="font-size:11px;">{{ auth()->user()->email ?? 'layan@sanduq.app' }}</span>
        </span>
    </div>
</header>
