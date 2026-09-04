<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * حسابات لوحة التحكم والتقارير.
 *
 * كل التجميعات تُنفَّذ داخل قاعدة البيانات عبر SUM و COUNT و GROUP BY، لا بجلب
 * الصفوف ومعالجتها في PHP — وهو شرط صريح في البند 4.1 من وثيقة SRS. الفهرس
 * المركّب (user_id, date) هو ما يجعل هذه الاستعلامات سريعة.
 */
class TransactionStats
{
    /**
     * إجماليات الدخل والمصروف وعدد الحركات في الفترة — البند 3.3.1.
     *
     * @return array{income: float, expense: float, incomeCount: int, expenseCount: int}
     */
    public function totals(User $user, Period $period, ?int $categoryId = null): array
    {
        $rows = $this->query($user, $period, $categoryId)
            ->selectRaw('type, SUM(amount) AS total, COUNT(*) AS cnt')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        return [
            'income' => (float) ($rows['income']->total ?? 0),
            'expense' => (float) ($rows['expense']->total ?? 0),
            'incomeCount' => (int) ($rows['income']->cnt ?? 0),
            'expenseCount' => (int) ($rows['expense']->cnt ?? 0),
        ];
    }

    /**
     * سلسلة الرسم البياني: دخل ومصروف لكل سلة زمنية — البند 3.3.4.
     *
     * نبني السلال كاملة من الفترة ثم نعبّئها بنتائج الاستعلام، فتظهر الفترات
     * التي لا حركة فيها بقيمة صفر بدل أن تختفي من المحور. هذا أيضًا يضمن أن
     * السلسلة لا تعود فارغة أبدًا، وهو ما يحتاجه مكوّن الرسم.
     *
     * @return list<array{label: string, income: float, expense: float}>
     */
    public function series(User $user, Period $period, ?int $categoryId = null): array
    {
        $expression = $period->sqlBucketExpression();

        $rows = $this->query($user, $period, $categoryId)
            ->selectRaw("{$expression} AS bucket, type, SUM(amount) AS total")
            ->groupByRaw("{$expression}, type")
            ->get();

        // نفهرس النتائج بمفتاح "التاريخ|النوع" للوصول السريع أثناء التعبئة.
        $totals = [];
        foreach ($rows as $row) {
            $totals[$row->bucket.'|'.$row->type] = (float) $row->total;
        }

        $series = [];
        foreach ($period->bucketStarts() as $bucketStart) {
            $key = $bucketStart->toDateString();

            $series[] = [
                'label' => $period->bucketLabel($bucketStart),
                'income' => $totals[$key.'|income'] ?? 0.0,
                'expense' => $totals[$key.'|expense'] ?? 0.0,
            ];
        }

        return $series;
    }

    /**
     * توزيع المبالغ على التصنيفات — البند 3.3.3.
     *
     * النسبة محسوبة من مجموع كل تصنيفات النوع في الفترة، لا من المعروض فقط،
     * حتى تبقى صادقة عند تحديد العدد بـ $limit.
     *
     * @return list<array{name: string, icon: string, total: float, pct: int}>
     */
    public function byCategory(
        User $user,
        Period $period,
        string $type,
        ?int $categoryId = null,
        ?int $limit = null,
    ): array {
        $nameColumn = app()->getLocale() === 'en' ? 'name_en' : 'name_ar';

        $rows = $this->query($user, $period, $categoryId)
            // العمودان type و user_id موجودان في الجدولين معًا، لذلك نؤهّل كل
            // عمود باسم جدوله بعد الضمّ تفاديًا لخطأ الغموض في SQL.
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.type', $type)
            ->selectRaw("categories.{$nameColumn} AS name, categories.icon AS icon, SUM(transactions.amount) AS total")
            ->groupBy('categories.id', "categories.{$nameColumn}", 'categories.icon')
            ->orderByDesc('total')
            ->get();

        $sum = (float) $rows->sum('total');

        if ($limit !== null) {
            $rows = $rows->take($limit);
        }

        return $rows->map(fn ($row) => [
            'name' => (string) $row->name,
            'icon' => (string) $row->icon,
            'total' => (float) $row->total,
            'pct' => $sum > 0 ? (int) round((float) $row->total / $sum * 100) : 0,
        ])->values()->all();
    }

    /**
     * أحدث الحركات في الفترة، لعرضها أسفل لوحة التحكم.
     *
     * @return Collection<int, \App\Models\Transaction>
     */
    public function recent(User $user, Period $period, ?int $categoryId = null, int $limit = 5): Collection
    {
        return $this->query($user, $period, $categoryId)
            ->with('category')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * نسبة التغيّر في الرصيد الصافي مقارنة بالفترة السابقة بنفس الطول.
     *
     * تُعيد null إذا لم يكن في الفترة السابقة رصيد يُقارَن به، فلا نعرض للمستخدم
     * رقمًا لا معنى له.
     */
    public function netChangePercent(User $user, Period $period, ?int $categoryId = null): ?float
    {
        $previous = $this->totals($user, $period->previous(), $categoryId);
        $previousNet = $previous['income'] - $previous['expense'];

        if (abs($previousNet) < 0.01) {
            return null;
        }

        $current = $this->totals($user, $period, $categoryId);
        $currentNet = $current['income'] - $current['expense'];

        return ($currentNet - $previousNet) / abs($previousNet) * 100;
    }

    /**
     * الاستعلام الأساسي: حركات هذا المستخدم داخل الفترة، مع فلتر تصنيف اختياري.
     *
     * البدء من $user->transactions() هو ما يضمن عزل البيانات (البند 4.3):
     * شرط user_id يُضاف تلقائيًا فلا يمكن نسيانه في أي استعلام.
     *
     * @return HasMany<\App\Models\Transaction>
     */
    private function query(User $user, Period $period, ?int $categoryId): HasMany
    {
        return $user->transactions()
            ->whereBetween('transactions.date', [
                $period->start->toDateString(),
                $period->end->toDateString(),
            ])
            ->when(
                $categoryId,
                fn ($query) => $query->where('transactions.category_id', $categoryId)
            );
    }
}
