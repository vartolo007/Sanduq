<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PasswordOtp;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * الخطوة الثانية من استعادة كلمة المرور: إدخال الرمز وكلمة المرور الجديدة.
 */
class NewPasswordController extends Controller
{
    /**
     * عرض نموذج الرمز وكلمة المرور الجديدة.
     *
     * البريد يأتي من الجلسة (وضعه PasswordOtpController) ليُملأ الحقل مسبقًا،
     * ويبقى قابلًا للتعديل إن فتح المستخدم الصفحة من متصفّح آخر.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'email' => $request->session()->get('otp_email', ''),
        ]);
    }

    /**
     * التحقق من الرمز وحفظ كلمة المرور الجديدة.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            // digits تضمن أرقامًا فقط بطول محدّد، فلا يمر رمز بأحرف أو بطول آخر.
            'code' => ['required', 'string', 'digits:'.PasswordOtp::LENGTH],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $email = mb_strtolower($request->string('email')->trim()->toString());

        // الفحص الأهم في هذا التدفق: بدونه يمكن تجربة المليون احتمال آليًا.
        if (PasswordOtp::tooManyAttempts($email)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'code' => __('app.otp_too_many', [
                        'minutes' => (int) ceil(PasswordOtp::secondsUntilRetry($email) / 60),
                    ]),
                ]);
        }

        $user = User::where('email', $email)->first();

        // رسالة واحدة لكل أسباب الفشل (رمز خاطئ، منتهٍ، أو بريد غير مسجّل)
        // حتى لا تميّز الردودُ الحالاتِ عن بعضها.
        if ($user === null || ! PasswordOtp::verify($email, $request->string('code')->toString())) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['code' => __('app.otp_invalid')]);
        }

        $user->forceFill([
            // التشفير يتم تلقائيًا بفضل 'password' => 'hashed' في نموذج User
            'password' => $request->string('password')->toString(),
            // إبطال "تذكّرني" على كل الأجهزة التي كانت مسجّلة الدخول
            'remember_token' => Str::random(60),
        ])->save();

        // الرمز صالح لمرة واحدة: نُبطله فور نجاح التغيير.
        PasswordOtp::clear($email);
        $request->session()->forget('otp_email');

        event(new PasswordReset($user));

        return redirect()
            ->route('login')
            ->with('status', __('app.password_reset_done'));
    }
}
