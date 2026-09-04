@props(['amount', 'signed' => false, 'type' => null])
@php
    $n = number_format(abs($amount), 2);
    $val = app()->getLocale() === 'ar' ? $n . ' ' . __('app.currency_short') : __('app.currency_short') . ' ' . $n;

    // signed=true: نعرض + أو − حسب نوع الحركة، كما في قوائم الحركات.
    // غير ذلك: نعرض − إذا كان المبلغ نفسه سالبًا. بدون هذا الفرع كان الرصيد
    // الصافي السالب يظهر موجبًا لأن abs() تحذف الإشارة.
    $sign = $signed
        ? ($type === 'income' ? '+ ' : '− ')
        : ($amount < 0 ? '− ' : '');
@endphp
<span class="sq-num">{{ $sign }}{{ $val }}</span>
