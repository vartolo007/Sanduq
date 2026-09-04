<?php

namespace App\Support;

/**
 * مصدر واحد لعناصر شريط التنقّل، يشترك فيه الشريط الجانبي على الشاشات
 * الكبيرة والشريط السفلي على الجوال. إضافة صفحة هنا تظهر في الاثنين معًا.
 *
 * كانت هذه القائمة في resources/views/components/nav-links.blade.php، لكن
 * مكوّنات Blade لها نطاق مستقل، فالمتغيّر المعرَّف داخل المكوّن لا يصل إلى
 * الملف الذي استدعاه. نقلها إلى PHP يجعل المشاركة صريحة وتعمل فعلًا.
 */
class Navigation
{
    /**
     * تُستدعى عند كل عرض لا مرة واحدة، حتى تتبع العناوين لغة الواجهة الحالية.
     *
     * @return array<int, array{route: string, label: string, icon: string}>
     */
    public static function items(): array
    {
        return [
            ['route' => 'dashboard',          'label' => __('app.nav_home'),         'icon' => 'home'],
            ['route' => 'transactions.index', 'label' => __('app.nav_transactions'), 'icon' => 'receipt'],
            ['route' => 'reports.index',      'label' => __('app.nav_reports'),      'icon' => 'chart'],
            ['route' => 'categories.index',   'label' => __('app.nav_categories'),   'icon' => 'tag'],
            ['route' => 'profile.edit',       'label' => __('app.nav_profile'),      'icon' => 'user'],
        ];
    }
}
