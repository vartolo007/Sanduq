<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * بيانات تجريبية لمراجعة التطبيق.
 *
 * الغرض أن يفتح المراجعُ اللوحةَ والتقارير فيجدها مليئة فورًا — أرقامًا
 * ورسمًا بيانيًا وتوزيعًا على التصنيفات — بدل أن يضطر إلى تسجيل حساب وإدخال
 * حركات بيده ليرى أن الميزات تعمل.
 *
 * الحركات موزّعة على ثمانية أشهر لا على شهر واحد، حتى تُظهر فلاتر الفترة
 * (الشهر الحالي، آخر ٦ أشهر، كل الفترات) نتائج مختلفة فعلًا، ويظهر الرسم
 * البياني تدرّجًا زمنيًا حقيقيًا.
 */
class DatabaseSeeder extends Seeder
{
    /** عدد الأشهر الماضية التي تُوزَّع عليها الحركات. */
    private const MONTHS = 8;

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@sanduq.test'],
            [
                'name' => 'مستخدم تجريبي',
                // التشفير تلقائي بفضل 'password' => 'hashed' في نموذج User
                'password' => 'password',
            ],
        );

        // التصنيفات الافتراضية تُنشأ عادةً عند التسجيل؛ ننشئها هنا للحساب
        // التجريبي لأنه لم يمر بذلك المسار.
        if ($user->categories()->doesntExist()) {
            Category::createDefaultsFor($user);
        }

        // التشغيل مرتين لا يضاعف البيانات.
        if ($user->transactions()->exists()) {
            $this->command?->warn('الحساب التجريبي يحتوي حركات مسبقًا — لم تُضف بيانات جديدة.');

            return;
        }

        $user->transactions()->createMany($this->transactionRows($user));

        $this->command?->info(sprintf(
            'أُنشئ حساب تجريبي (%s / password) مع %d حركة موزّعة على %d أشهر.',
            $user->email,
            $user->transactions()->count(),
            self::MONTHS,
        ));
    }

    /**
     * يبني صفوف الحركات لكل شهر من الأشهر الماضية.
     *
     * @return list<array<string, mixed>>
     */
    private function transactionRows(User $user): array
    {
        // mt_srand يجعل البيانات التجريبية متطابقة في كل تشغيل، فيسهل وصف ما
        // يراه المراجع. مقبول هنا لأنها أرقام عرض لا قيم أمنية — بخلاف رمز
        // الاستعادة الذي يستخدم random_int المولّد الآمن تشفيريًا.
        mt_srand(20260904);

        $categories = $user->categories()->get()->keyBy('name_en');
        $rows = [];

        for ($monthsAgo = self::MONTHS - 1; $monthsAgo >= 0; $monthsAgo--) {
            $month = Carbon::today()->subMonths($monthsAgo)->startOfMonth();

            // الشهر الحالي ناقص، فلا نولّد فيه حركات بتواريخ مستقبلية.
            $lastDay = $month->isSameMonth(Carbon::today())
                ? Carbon::today()->day
                : $month->daysInMonth;

            foreach ($this->monthlyPlan() as [$nameEn, $count, $min, $max, $dayHint]) {
                $category = $categories->get($nameEn);

                if ($category === null) {
                    continue;
                }

                for ($i = 0; $i < $count; $i++) {
                    $day = $dayHint ?? mt_rand(1, 28);

                    if ($day > $lastDay) {
                        continue;
                    }

                    $rows[] = [
                        'category_id' => $category->id,
                        'type' => $category->type,
                        // نقرّب إلى أقرب ٥٠٠ ليرة فتبدو المبالغ طبيعية
                        // بدل أرقام عشوائية بكسور غريبة.
                        'amount' => round(mt_rand($min, $max) / 500) * 500,
                        'date' => $month->copy()->day($day)->toDateString(),
                        'note' => null,
                    ];
                }
            }
        }

        return $rows;
    }

    /**
     * خطة الشهر الواحد: [اسم التصنيف، عدد الحركات، أقل مبلغ، أعلى مبلغ، يوم ثابت أو null].
     *
     * الرواتب والإيجار بتواريخ ثابتة كما في الواقع، والباقي موزّع عشوائيًا
     * داخل الشهر. الدخل أعلى من المصروف في المجمل ليظهر رصيد صافٍ موجب
     * ونسبة ادخار معقولة، مع تفاوت شهري يجعل مؤشّر التغيّر ذا معنى.
     *
     * @return list<array{0: string, 1: int, 2: int, 3: int, 4: int|null}>
     */
    private function monthlyPlan(): array
    {
        return [
            // دخل
            ['Salary',     1, 1_150_000, 1_250_000, 1],
            ['Freelance',  1,   150_000,   420_000, null],

            // مصروف
            ['Rent',       1,   380_000,   400_000, 3],
            ['Food',       5,    18_000,    65_000, null],
            ['Transport',  4,     6_000,    22_000, null],
            ['Bills',      1,    35_000,    95_000, 12],
            ['Shopping',   2,    45_000,   210_000, null],
            ['Health',     1,    40_000,   140_000, null],
        ];
    }
}
