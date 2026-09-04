<?php

namespace App\Support;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * الفترة الزمنية التي تُحسب على أساسها كل الأرقام — البند 3.3.2.
 *
 * تجمع ثلاثة أشياء في مكان واحد:
 *   1. بداية الفترة ونهايتها
 *   2. حجم السلة في الرسم البياني (يوم / أسبوع / شهر / سنة)
 *   3. تعبير SQL الذي يجمّع الصفوف حسب تلك السلة
 *
 * تُبنى من الـ query string، فتبقى الحالة في الرابط ويمكن مشاركته أو تحديث
 * الصفحة دون فقدان الاختيار.
 */
final class Period
{
    /**
     * المدى الافتراضي عند فتح الصفحة بلا اختيار.
     *
     * "كل الفترات" لا "الشهر الحالي": البند 3.3.1 من وثيقة SRS ينص على أن
     * الأرقام المعروضة تكون لكامل الفترة منذ بداية استخدام التطبيق ما لم
     * يطبّق المستخدم فلترًا مختلفًا.
     */
    public const DEFAULT_RANGE = 'all';

    /**
     * المدَيات الجاهزة. الثلاثة الأولى بعد 30d منصوص عليها في البند 3.3.2،
     * و"مخصصة" تغطي أي مدة أخرى يريدها المستخدم (آخر ستة أيام مثلًا).
     *
     * @var list<string>
     */
    public const RANGES = ['30d', 'month', '6m', 'all', 'custom'];

    /** @var list<string> */
    public const BUCKETS = ['day', 'week', 'month', 'year'];

    private function __construct(
        public readonly string $range,
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
        public readonly string $bucket,
        /** صحيح إذا اختار المستخدم حجم السلة بنفسه بدل الاشتقاق التلقائي. */
        public readonly bool $bucketWasChosen,
    ) {}

    /**
     * يبني الفترة من معطيات الطلب.
     *
     * $user مطلوب لأن مدى "كل الفترات" يبدأ من أول حركة سجّلها هذا المستخدم.
     */
    public static function fromRequest(Request $request, User $user): self
    {
        $range = $request->string('range')->toString();

        if (! in_array($range, self::RANGES, true)) {
            $range = self::DEFAULT_RANGE;
        }

        $today = CarbonImmutable::today();

        [$start, $end] = match ($range) {
            '30d' => [$today->subDays(29), $today],
            'month' => [$today->startOfMonth(), $today],
            '6m' => [$today->subMonths(5)->startOfMonth(), $today],
            'all' => [self::firstTransactionDate($user) ?? $today, $today],
            'custom' => self::customRange($request, $today),
        };

        // حماية من مدى مقلوب لو أدخل المستخدم تاريخ بداية بعد تاريخ النهاية.
        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        $chosen = $request->string('bucket')->toString();
        $bucketWasChosen = in_array($chosen, self::BUCKETS, true);

        return new self(
            range: $range,
            start: $start,
            end: $end,
            bucket: $bucketWasChosen ? $chosen : self::autoBucket($start, $end),
            bucketWasChosen: $bucketWasChosen,
        );
    }

    /**
     * يشتق حجم السلة من طول المدة.
     *
     * الهدف أن يبقى عدد الأعمدة مقروءًا دائمًا: لا عمود واحد لمدة قصيرة،
     * ولا مئات الأعمدة لمدة طويلة.
     */
    private static function autoBucket(CarbonImmutable $start, CarbonImmutable $end): string
    {
        $days = $start->diffInDays($end) + 1;

        return match (true) {
            $days <= 31 => 'day',    // حتى 31 عمودًا
            $days <= 120 => 'week',  // حتى 18 عمودًا
            $days <= 1095 => 'month', // حتى 36 عمودًا
            default => 'year',
        };
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private static function customRange(Request $request, CarbonImmutable $today): array
    {
        $from = self::parseDate($request->string('from')->toString());
        $to = self::parseDate($request->string('to')->toString());

        // إن نقص أحد الطرفين نكمله بقيمة معقولة بدل رفض الطلب.
        return [
            $from ?? ($to?->subDays(29) ?? $today->subDays(29)),
            $to ?? $today,
        ];
    }

    private static function parseDate(string $value): ?CarbonImmutable
    {
        if ($value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function firstTransactionDate(User $user): ?CarbonImmutable
    {
        $first = $user->transactions()->min('date');

        return $first ? CarbonImmutable::parse($first) : null;
    }

    /**
     * تعبير SQL الذي يحوّل عمود التاريخ إلى بداية السلة.
     *
     * القيم ثابتة ومختارة من قائمة مغلقة، فلا مدخل مستخدم يصل إلى الاستعلام.
     */
    public function sqlBucketExpression(): string
    {
        return match ($this->bucket) {
            'day' => 'DATE(`date`)',
            // WEEKDAY يعيد 0 ليوم الاثنين، فطرحه يرجعنا إلى بداية الأسبوع
            'week' => 'DATE(DATE_SUB(`date`, INTERVAL WEEKDAY(`date`) DAY))',
            'month' => "DATE_FORMAT(`date`, '%Y-%m-01')",
            'year' => "DATE_FORMAT(`date`, '%Y-01-01')",
        };
    }

    /**
     * كل بدايات السلال ضمن الفترة، بالترتيب.
     *
     * نولّدها كاملة حتى تظهر السلال الفارغة في الرسم بقيمة صفر بدل أن تُحذف،
     * فيبقى المحور الزمني متصلًا.
     *
     * @return list<CarbonImmutable>
     */
    public function bucketStarts(): array
    {
        $cursor = $this->alignToBucket($this->start);
        $last = $this->alignToBucket($this->end);

        $out = [];

        while ($cursor->lessThanOrEqualTo($last)) {
            $out[] = $cursor;
            $cursor = match ($this->bucket) {
                'day' => $cursor->addDay(),
                'week' => $cursor->addWeek(),
                'month' => $cursor->addMonth(),
                'year' => $cursor->addYear(),
            };
        }

        return $out;
    }

    private function alignToBucket(CarbonImmutable $date): CarbonImmutable
    {
        return match ($this->bucket) {
            'day' => $date->startOfDay(),
            'week' => $date->startOfWeek(CarbonImmutable::MONDAY),
            'month' => $date->startOfMonth(),
            'year' => $date->startOfYear(),
        };
    }

    /**
     * عنوان السلة كما يظهر تحت الرسم البياني.
     */
    public function bucketLabel(CarbonImmutable $bucketStart): string
    {
        $spansYears = $this->start->year !== $this->end->year;

        return match ($this->bucket) {
            'day', 'week' => $bucketStart->translatedFormat('j M'),
            'month' => $bucketStart->translatedFormat($spansYears ? 'M y' : 'M'),
            'year' => $bucketStart->format('Y'),
        };
    }

    /**
     * الفترة السابقة مباشرة بنفس الطول، للمقارنة في بطاقة الرصيد.
     */
    public function previous(): self
    {
        $length = $this->start->diffInDays($this->end);

        return new self(
            range: $this->range,
            start: $this->start->subDays($length + 1),
            end: $this->start->subDay(),
            bucket: $this->bucket,
            bucketWasChosen: $this->bucketWasChosen,
        );
    }

    /**
     * وصف الفترة بالعربية أو الإنجليزية، يُعرض في عنوان الرسم وبطاقة الرصيد.
     */
    public function label(): string
    {
        return match ($this->range) {
            '30d' => __('app.range_30d'),
            'month' => __('app.range_month'),
            '6m' => __('app.range_6m'),
            'all' => __('app.range_all'),
            // نحذف السنة من الطرف الأول إن كان الطرفان في السنة نفسها،
            // ونستخدم شرطة قصيرة كي لا تلتبس بالشرطة الطويلة في عنوان الرسم.
            'custom' => $this->start->translatedFormat($this->start->year === $this->end->year ? 'j M' : 'j M Y')
                .' – '.$this->end->translatedFormat('j M Y'),
        };
    }

    /**
     * اسم حجم السلة، يُعرض بجانب أزرار التقسيم.
     */
    public function bucketLabelName(): string
    {
        return __('app.bucket_'.$this->bucket);
    }

    /**
     * المعطيات التي تُعاد إلى الرابط عند بناء روابط الفلاتر.
     *
     * @return array<string, string>
     */
    public function queryParams(): array
    {
        $params = ['range' => $this->range];

        if ($this->range === 'custom') {
            $params['from'] = $this->start->toDateString();
            $params['to'] = $this->end->toDateString();
        }

        if ($this->bucketWasChosen) {
            $params['bucket'] = $this->bucket;
        }

        return $params;
    }
}
