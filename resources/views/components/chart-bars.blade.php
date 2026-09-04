@props(['series'])
@php
    // max() على مصفوفة فارغة ترمي ValueError في PHP 8، لذلك نتحقق أولًا.
    // الحالة نادرة لأن TransactionStats::series تعيد السلال كاملة دائمًا،
    // لكن المكوّن يجب أن يصمد أمام أي مصدر بيانات.
    $peaks = array_map(fn ($m) => max($m['income'], $m['expense']), $series);
    $max = $peaks ? (max($peaks) ?: 1) : 1;

    // مع التقسيم اليومي قد تصل السلال إلى 31، وطباعة عنوان تحت كل عمود تجعل
    // المحور غير مقروء. نعرض عنوانًا كل خطوة بحيث لا تتجاوز العناوين ثمانية،
    // ونُبقي بقية الخانات فارغة حتى تبقى محاذاة الأعمدة سليمة.
    $labelStep = max(1, (int) ceil(count($series) / 8));

    // المسافة بين الأعمدة في CSS مضبوطة على 8px، وهي مناسبة لستة أعمدة شهرية.
    // مع تقسيم يومي (حتى 31 عمودًا) تلتهم الفواصل عرض الرسم كله فتصير الأعمدة
    // بعرض صفر وتختفي. لذلك نُصغّر الفاصل كلما زاد عدد السلال.
    $count = count($series);
    $gap = $count > 24 ? 2 : ($count > 12 ? 4 : 8);
    $innerGap = $count > 24 ? 2 : ($count > 12 ? 3 : 4);
@endphp
@if (empty($series))
    <p class="sq-mute" style="margin:0;font-size:14px;">{{ __('app.no_data') }}</p>
@else
    {{-- Pure CSS bars: they reflow with the container, so they never overflow on mobile
         and need no RTL-specific handling — the flex row simply reverses. --}}
    <div class="sq-chart" style="gap:{{ $gap }}px;">
        @foreach ($series as $m)
            <div class="sq-chart-col" style="gap:{{ $innerGap }}px;">
                <span class="sq-bar sq-bar-in"  style="height:{{ round($m['income'] / $max * 100) }}%"
                      title="{{ $m['label'] }} · {{ __('app.income') }}: {{ number_format($m['income'], 2) }}"></span>
                <span class="sq-bar sq-bar-out" style="height:{{ round($m['expense'] / $max * 100) }}%"
                      title="{{ $m['label'] }} · {{ __('app.expense') }}: {{ number_format($m['expense'], 2) }}"></span>
            </div>
        @endforeach
    </div>
    {{-- على شاشة الجوال لا يتسع مكان ثمانية عناوين، فنعلّم البدائل منها بصنف
         يخفيها CSS تحت 680px ويُبقي أربعة فقط — مع إبقاء الخانة نفسها قائمة
         حتى لا تختل محاذاة الأعمدة. --}}
    <div class="sq-axis" style="gap:{{ $gap }}px;">
        @foreach ($series as $i => $m)
            @php $isLabel = $i % $labelStep === 0; @endphp
            <div @class(['sq-axis-dense' => $isLabel && intdiv($i, $labelStep) % 2 === 1])>{{ $isLabel ? $m['label'] : '' }}</div>
        @endforeach
    </div>
@endif
