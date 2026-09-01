@props(['series'])
@php $max = max(array_map(fn ($m) => max($m['income'], $m['expense']), $series)) ?: 1; @endphp
{{-- Pure CSS bars: they reflow with the container, so they never overflow on mobile
     and need no RTL-specific handling — the flex row simply reverses. --}}
<div class="sq-chart">
    @foreach ($series as $m)
        <div class="sq-chart-col">
            <span class="sq-bar sq-bar-in"  style="height:{{ round($m['income'] / $max * 100) }}%"
                  title="{{ __('app.income') }}: {{ number_format($m['income'], 2) }}"></span>
            <span class="sq-bar sq-bar-out" style="height:{{ round($m['expense'] / $max * 100) }}%"
                  title="{{ __('app.expense') }}: {{ number_format($m['expense'], 2) }}"></span>
        </div>
    @endforeach
</div>
<div class="sq-axis">
    @foreach ($series as $m)<div>{{ $m['label'] }}</div>@endforeach
</div>
