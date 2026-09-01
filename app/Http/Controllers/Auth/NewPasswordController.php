<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * الخطوة الثانية من استعادة كلمة المرور: تعيين كلمة جديدة عبر الرابط المُرسل.
 *
 * ملاحظة على الأسماء: Password (الواجهة) تدير رموز الاستعادة، بينما
 * PasswordRule (قاعدة التحقق) تفحص قوة كلمة المرور. الاسمان متشابهان في
 * Laravel لذلك أعطينا الثانية اسمًا مستعارًا في سطر use أعلاه.
 */
class NewPasswordController extends Controller
{
    /**
     * عرض نموذج كلمة المرور الجديدة.
     *
     * الرمز يأتي من الرابط، والبريد من الـ query string، ونمرّرهما إلى النموذج
     * ليعودا معه كحقلين مخفيين عند الإرسال.
     */
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    /**
     * التحقق من الرمز وحفظ كلمة المرور الجديدة.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        // reset تتحقق من صلاحية الرمز ومن مطابقته للبريد، ثم تنادي دالتنا
        // لتغيير كلمة المرور. إن كان الرمز خاطئًا أو منتهيًا لا تُنفَّذ الدالة.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    // التشفير يتم تلقائيًا بفضل 'password' => 'hashed' في نموذج User
                    'password' => $request->string('password')->toString(),
                    // إبطال "تذكّرني" على كل الأجهزة التي كانت مسجّلة الدخول
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
