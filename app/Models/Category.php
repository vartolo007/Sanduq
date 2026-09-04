<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    /**
     * الحقول المسموح تعبئتها دفعة واحدة عبر create() أو update().
     *
     * user_id غير مذكور عمدًا: نضبطه دائمًا من المستخدم المسجّل في الكنترولر،
     * فلا يستطيع أحد إرسال user_id في النموذج ليضيف تصنيفًا لحساب غيره.
     */
    protected $fillable = [
        'name_ar',
        'name_en',
        'type',
        'icon',
    ];

    /**
     * الاسم بلغة الواجهة الحالية.
     *
     * خاصية محسوبة لا عمود في الجدول: عند كتابة {{ $category->name }} في Blade
     * يستدعي Eloquent هذه الدالة ويعيد العمود المناسب للغة المختارة.
     */
    protected function name(): Attribute
    {
        return Attribute::get(
            fn (): string => app()->getLocale() === 'en' ? $this->name_en : $this->name_ar
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * التصنيفات الافتراضية التي يبدأ بها كل حساب جديد.
     *
     * الغرض منها أن يجد المستخدم ما يسجّل عليه فور دخوله، بدل صفحة فارغة
     * تُجبره على إنشاء تصنيف قبل أول عملية. يبقى بإمكانه إضافة غيرها.
     *
     * @return array<int, array{name_ar: string, name_en: string, type: string, icon: string}>
     */
    public static function defaults(): array
    {
        return [
            ['name_ar' => 'راتب',      'name_en' => 'Salary',      'type' => 'income',  'icon' => 'briefcase'],
            ['name_ar' => 'عمل حر',    'name_en' => 'Freelance',   'type' => 'income',  'icon' => 'wallet'],
            ['name_ar' => 'هدية',      'name_en' => 'Gift',        'type' => 'income',  'icon' => 'gift'],
            ['name_ar' => 'استثمار',   'name_en' => 'Investment',  'type' => 'income',  'icon' => 'trend'],

            ['name_ar' => 'طعام',      'name_en' => 'Food',        'type' => 'expense', 'icon' => 'cup'],
            ['name_ar' => 'مواصلات',   'name_en' => 'Transport',   'type' => 'expense', 'icon' => 'car'],
            ['name_ar' => 'إيجار',     'name_en' => 'Rent',        'type' => 'expense', 'icon' => 'home2'],
            ['name_ar' => 'تسوّق',     'name_en' => 'Shopping',    'type' => 'expense', 'icon' => 'cart'],
            ['name_ar' => 'فواتير',    'name_en' => 'Bills',       'type' => 'expense', 'icon' => 'bolt'],
            ['name_ar' => 'صحة',       'name_en' => 'Health',      'type' => 'expense', 'icon' => 'heart'],
            ['name_ar' => 'تعليم',     'name_en' => 'Education',   'type' => 'expense', 'icon' => 'book'],
        ];
    }

    /**
     * ينشئ التصنيفات الافتراضية لمستخدم جديد.
     */
    public static function createDefaultsFor(User $user): void
    {
        $user->categories()->createMany(static::defaults());
    }
}
