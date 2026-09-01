@props(['amount', 'signed' => false, 'type' => null])
@php
    $n = number_format(abs($amount), 2);
    $val = app()->getLocale() === 'ar' ? $n . ' ' . __('app.currency_short') : __('app.currency_short') . ' ' . $n;
    $sign = $signed ? ($type === 'income' ? '+ ' : '− ') : '';
@endphp
<span class="sq-num">{{ $sign }}{{ $val }}</span>
