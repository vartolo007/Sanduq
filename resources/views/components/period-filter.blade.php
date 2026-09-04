@props(['period', 'routeName'])

@php
    use App\Support\Period;

    $ranges = [
        'month'  => __('app.range_month'),
        '30d'    => __('app.range_30d'),
        '6m'     => __('app.range_6m'),
        'all'    => __('app.range_all'),
        'custom' => __('app.range_custom'),
    ];

    // نحتفظ بباقي معطيات الرابط (فلتر التصنيف مثلًا) عند تبديل الفترة،
    // ونحذف ما يخص الفترة نفسها والصفحة حتى لا تتراكم قيم قديمة.
    $keep = request()->except(['page', 'range', 'from', 'to', 'bucket']);

    // روابط التقسيم تحافظ على الفترة المختارة
    $keepWithRange = array_merge($keep, $period->queryParams());
    unset($keepWithRange['bucket']);
@endphp

<x-card pad="18px">
    <div class="sq-kicker">{{ __('app.filters') }}</div>

    {{-- المدى الزمني — البند 3.3.2 --}}
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
        @foreach ($ranges as $key => $label)
            <a href="{{ route($routeName, array_merge($keep, ['range' => $key])) }}"
               class="sq-chip sq-tap @if($period->range === $key) is-on @endif"
               @if($period->range === $key) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </div>

    {{-- تاريخا البداية والنهاية، يظهران عند اختيار "مخصصة" --}}
    @if ($period->range === 'custom')
        <form method="GET" action="{{ route($routeName) }}" class="sq-grid sq-g2" style="gap:12px;align-items:end;">
            @foreach ($keep as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <input type="hidden" name="range" value="custom">

            <div class="field">
                <label for="pf-from">{{ __('app.from') }}</label>
                <input id="pf-from" class="input" type="date" name="from"
                       value="{{ request('from', $period->start->toDateString()) }}" style="min-height:44px;">
            </div>
            <div class="field">
                <label for="pf-to">{{ __('app.to') }}</label>
                <input id="pf-to" class="input" type="date" name="to"
                       value="{{ request('to', $period->end->toDateString()) }}" style="min-height:44px;">
            </div>
            <div>
                <button type="submit" class="btn btn-secondary sq-tap" style="min-height:44px;">{{ __('app.apply') }}</button>
            </div>
        </form>
    @endif

    {{-- حجم السلة في الرسم البياني.
         "تلقائي" يشتقّه من طول المدة، ويبقى للمستخدم أن يفرض تقسيمًا آخر. --}}
    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
        <span class="sq-mute" style="font-size:12px;">{{ __('app.grouping') }}</span>

        <a href="{{ route($routeName, $keepWithRange) }}"
           class="sq-chip sq-tap @if(! $period->bucketWasChosen) is-on @endif"
           style="min-height:34px;padding:0 12px;font-size:13px;">
            {{ __('app.grouping_auto') }}
            @if (! $period->bucketWasChosen)
                <span style="opacity:.7;margin-inline-start:6px;">· {{ $period->bucketLabelName() }}</span>
            @endif
        </a>

        @foreach (Period::BUCKETS as $bucket)
            <a href="{{ route($routeName, array_merge($keepWithRange, ['bucket' => $bucket])) }}"
               class="sq-chip sq-tap @if($period->bucketWasChosen && $period->bucket === $bucket) is-on @endif"
               style="min-height:34px;padding:0 12px;font-size:13px;">{{ __('app.bucket_'.$bucket) }}</a>
        @endforeach
    </div>

    {{ $slot ?? '' }}
</x-card>
