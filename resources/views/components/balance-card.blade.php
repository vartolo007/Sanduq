@props(['balance', 'incomeShare' => 50, 'delta' => '+12.4%', 'savings' => '0%'])
<section class="blueprint sq-hero sq-lift">
    <x-corners />
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;">
        <div>
            <div class="sq-label" style="opacity:.62;color:inherit;">{{ __('app.net_balance') }}</div>
            <div class="sq-hero-num" style="margin-top:6px;">{{ $balance }}</div>
        </div>
        <span class="tag" style="border:1px solid rgba(242,244,246,.35);color:#f2f4f6;background:transparent;flex:none;">
            {{ __('app.this_month') }}
        </span>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:8px 24px;font-size:13px;opacity:.8;">
        <span>{{ __('app.trend_lead') }} <strong style="font-family:var(--font-heading);">{{ $delta }}</strong> {{ __('app.vs_last_month') }}</span>
        <span>{{ __('app.savings_rate') }} <strong style="font-family:var(--font-heading);">{{ $savings }}</strong></span>
    </div>
    <div class="sq-meter"><span style="width:{{ $incomeShare }}%;"></span></div>
</section>
