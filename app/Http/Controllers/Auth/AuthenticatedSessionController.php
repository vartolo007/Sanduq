<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * تسجيل الدخول والخروج — المتطلبان 3.1.2 و 3.1.3.
 *
 * سُمّي "Session" لأن ما نفعله فعليًا هو إنشاء جلسة للمستخدم أو إنهاؤها.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * عرض نموذج تسجيل الدخول.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * تنفيذ تسجيل الدخول — المتطلب 3.1.2.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // كل المنطق داخل LoginRequest::authenticate().
        // إذا فشل، يرمي ValidationException ويعيد Laravel المستخدم تلقائيًا
        // إلى الصفحة السابقة مع رسالة الخطأ.
        $request->authenticate();

        // intended() تعيد المستخدم إلى الصفحة التي كان يحاول الوصول إليها
        // قبل أن يوقفه الـ middleware، أو إلى لوحة التحكم إن لم توجد.
        return redirect()->intended(route('dashboard'));
    }

    /**
     * تسجيل الخروج — المتطلب 3.1.3.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        // إبطال الجلسة وإعادة توليد رمز CSRF حتى لا تبقى الجلسة القديمة صالحة.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
