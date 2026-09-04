@props([
    // المبلغ رقمًا. كانت البطاقة تستقبل HTML جاهزًا عبر view()->render()، لكن
    // Blade يهرّب النصوص عند الإخراج فكان الوسم يظهر للمستخدم حرفيًا.
    // استقبال الرقم ورسمه هنا بـ x-money أوضح وأسلم.
    'amount' => 0,
    'incomeShare' => 50,
    'savings' => '0%',
    'periodLabel' => '',
    // نسبة تغيّر الرصيد الصافي عن الفترة السابقة. null تعني أن الفترة السابقة
    // خالية فلا مقارنة ممكنة — عندها نخفي السطر بدل عرض رقم بلا معنى.
    'netChange' => null,
])
@php
    $hasChange = $netChange !== null;

    if ($hasChange) {
        $abs = abs($netChange);
        // النِسب الكبيرة لا تحتاج كسورًا عشرية، والصغيرة تحتاجها.
        $changeText = ($netChange >= 0 ? '+' : '−').number_format($abs, $abs >= 100 ? 0 : 1).'%';
    }
@endphp
<section class="blueprint sq-hero sq-lift">
    <x-corners />
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;">
        <div>
            <div class="sq-label" style="opacity:.62;color:inherit;">{{ __('app.net_balance') }}</div>
            <div class="sq-hero-num" style="margin-top:6px;"><x-money :amount="$amount" /></div>
        </div>
        <span class="tag" style="border:1px solid rgba(242,244,246,.35);color:#f2f4f6;background:transparent;flex:none;">
            {{ $periodLabel }}
        </span>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:8px 24px;font-size:13px;opacity:.8;">
        @if ($hasChange)
            <span>{{ __('app.trend_lead') }} <strong style="font-family:var(--font-heading);">{{ $changeText }}</strong> {{ __('app.vs_last_month') }}</span>
        @endif
        <span>{{ __('app.savings_rate') }} <strong style="font-family:var(--font-heading);">{{ $savings }}</strong></span>
    </div>
    <div class="sq-meter"><span style="width:{{ $incomeShare }}%;"></span></div>
</section>
