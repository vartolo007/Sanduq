<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| مسارات التطبيق
|--------------------------------------------------------------------------
|
| أسماء المسارات مطلوبة بصيغتها هذه لأن مكوّنات Blade تناديها مباشرة.
|
*/

Route::get('/', fn () => redirect()->route('login'));

// تبديل اللغة: يخزّن الاختيار في الجلسة ثم يعيد المستخدم إلى الصفحة السابقة.
// يقرأه SetLocale middleware في بداية كل طلب.
Route::get('locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['ar', 'en'], true), 404);
    session(['locale' => $locale]);

    return back();
})->name('locale.switch');

require __DIR__.'/auth.php';
