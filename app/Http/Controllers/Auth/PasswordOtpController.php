<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PasswordOtp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * الخطوة الأولى من استعادة كلمة المرور: إرسال رمز من ٦ خانات إلى البريد.
 */
class PasswordOtpController extends Controller
{
    /**
     * عرض نموذج "نسيت كلمة المرور".
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * توليد رمز وإرساله على بريد المستخدم.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // نوحّد حالة الأحرف: البريد غير حسّاس لها، وبدون التوحيد يصبح
        // "A@b.com" و "a@b.com" مفتاحَين مختلفَين في عدّادات التقييد.
        $email = mb_strtolower($request->string('email')->trim()->toString());

        // منع إغراق بريد المستخدم وإرهاق الخادم بطلبات متتابعة.
        if (! PasswordOtp::canSend($email)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __('app.otp_resend_wait', [
                        'seconds' => PasswordOtp::secondsUntilResend($email),
                    ]),
                ]);
        }

        // لا نرسل شيئًا لبريد غير مسجّل، لكن الرد الظاهر واحد في الحالتين حتى
        // لا تُستخدم هذه الصفحة لاكتشاف البُرد المسجّلة (Email Enumeration).
        if (User::where('email', $email)->exists()) {
            PasswordOtp::send($email);
        }

        // نحمل البريد في الجلسة لا في الرابط: وضعه في الـ query string يسرّبه
        // إلى سجلات الخادم وسجل التصفّح بلا داعٍ.
        $request->session()->put('otp_email', $email);

        return redirect()
            ->route('password.reset')
            ->with('status', __('app.otp_sent'));
    }
}
