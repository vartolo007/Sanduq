<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordOtpController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| مسارات المصادقة
|--------------------------------------------------------------------------
|
| أسماء المسارات هنا (login, register, logout, password.request …) مطلوبة
| بهذه الصيغة تحديدًا لأن ملفات Blade تناديها مباشرة عبر route('login').
|
| middleware('guest')  → للزوار فقط. من سجّل دخوله وحاول فتح صفحة الدخول
|                        يُعاد توجيهه إلى لوحة التحكم.
| middleware('auth')   → لمن سجّل دخوله فقط.
|
| لاحظ أن مسار GET ومسار POST يتشاركان نفس الرابط (مثلًا /login): الأول
| يعرض النموذج والثاني يستقبله. لهذا نسمّي الأول فقط — لأن route('login')
| تُرجع الرابط، والمتصفح هو من يحدّد نوع الطلب من الـ form.
|
*/

Route::middleware('guest')->group(function () {

    // إنشاء حساب — المتطلب 3.1.1
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // تسجيل الدخول — المتطلب 3.1.2
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // استعادة كلمة المرور: الخطوة الأولى — إرسال رمز من ٦ خانات على البريد
    Route::get('forgot-password', [PasswordOtpController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordOtpController::class, 'store'])->name('password.email');

    // استعادة كلمة المرور: الخطوة الثانية — إدخال الرمز وكلمة المرور الجديدة.
    // لا معرّف في الرابط: الرمز يكتبه المستخدم في النموذج.
    Route::get('reset-password', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// تسجيل الخروج — المتطلب 3.1.3
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
