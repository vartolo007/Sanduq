@props(['label', 'value', 'meta' => null, 'tone' => 'income'])
@php $color = $tone === 'income' ? 'var(--sq-in)' : 'var(--sq-out)'; @endphp
<div class="card blueprint sq-lift" style="padding:18px;gap:10px;">
    <x-corners />
    <div class="sq-row" style="gap:8px;">
        <span class="sq-icon-box" style="width:26px;height:26px;color:{{ $color }};border-color:color-mix(in srgb,{{ $color }} 45%,transparent);">
            <x-icon :name="$tone === 'income' ? 'up' : 'down'" size="16" />
        </span>
        <span class="sq-label">{{ $label }}</span>
    </div>
    <div class="sq-stat-num" style="color:{{ $color }};">{{ $value }}</div>
    @if ($meta)<div class="sq-mute" style="font-size:12px;">{{ $meta }}</div>@endif
</div>
