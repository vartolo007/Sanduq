<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| مسارات التطبيق
|--------------------------------------------------------------------------
|
| أسماء المسارات مطلوبة بصيغتها هذه لأن مكوّنات Blade تناديها مباشرة،
| خصوصًا components/nav-links.blade.php التي تبني شريط التنقّل.
|
*/

Route::get('/', fn () => redirect()->route('dashboard'));

// تبديل اللغة: يخزّن الاختيار في الجلسة ثم يعيد المستخدم إلى الصفحة السابقة.
// يقرأه SetLocale middleware في بداية كل طلب.
Route::get('locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['ar', 'en'], true), 404);
    session(['locale' => $locale]);

    return back();
})->name('locale.switch');

/*
 * كل ما تحت هذه المجموعة يتطلب تسجيل دخول — المتطلب 3.1.4.
 * أي محاولة وصول بدون جلسة يعيدها middleware 'auth' إلى صفحة الدخول.
 */
Route::middleware('auth')->group(function () {

    // لوحة التحكم — البند 3.3.1
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // التقارير — البنود 3.3.2 و 3.3.3 و 3.3.4
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // العمليات المالية — المتطلبات 3.2.1 إلى 3.2.4.
    // except('show') لأن التطبيق لا يعرض صفحة مستقلة لحركة واحدة.
    Route::resource('transactions', TransactionController::class)->except(['show']);

    // التصنيفات — المتطلب 3.2.5: إضافة وعرض فقط، بلا صفحة تعديل.
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    // صفحة الحساب — خارج نطاق SRS، أُضيفت بطلب صاحب المشروع
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
