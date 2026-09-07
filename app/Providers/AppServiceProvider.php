<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Transaction;
use App\Policies\CategoryPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // القالب الافتراضي للترقيم في Laravel مكتوب بـ Tailwind، وهو غير محمّل
        // في هذا المشروع، فنستبدله بقالبنا المبني على أصناف sanduq.css.
        Paginator::defaultView('vendor.pagination.sanduq');

        // ربط صريح بين كل نموذج وسياسته.
        // Laravel يستطيع اكتشافها تلقائيًا من تطابق الأسماء، لكن الاكتشاف يعتمد
        // على مُحمِّل الأصناف: إن لم يُحدَّث autoload على السيرفر، أو اختلفت حالة
        // أحرف اسم الملف على نظام ملفات حسّاس لها، لا يجد Laravel السياسة
        // فيرفض كل العمليات بـ 403 دون رسالة توضّح السبب. التسجيل الصريح
        // يجعل الربط مستقلًا عن ذلك كله.
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
    }
}
