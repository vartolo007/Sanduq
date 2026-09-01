<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * قواعد ومنطق تسجيل الدخول — المتطلب 3.1.2 في وثيقة SRS.
 *
 * وضعنا محاولة تسجيل الدخول نفسها داخل هذا الملف (وليس في الـ Controller)
 * لأنها جزء من التحقق من صحة المدخلات: إما أن تكون البيانات صحيحة فيدخل
 * المستخدم، أو تفشل فنرمي ValidationException كأي خطأ تحقق آخر.
 */
class LoginRequest extends FormRequest
{
    /**
     * عدد المحاولات الفاشلة المسموح بها قبل الحظر المؤقت.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * مدة الحظر بالثواني بعد استنفاد المحاولات.
     */
    private const LOCKOUT_SECONDS = 60;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * يحاول تسجيل دخول المستخدم بالبيانات المُرسلة.
     *
     * @throws ValidationException إذا كانت البيانات خاطئة أو تجاوز المستخدم عدد المحاولات
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Auth::attempt يقارن كلمة المرور المُدخلة مع النسخة المشفّرة في قاعدة البيانات.
        // المعامل الثاني (boolean) يفعّل خاصية "تذكّرني".
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), self::LOCKOUT_SECONDS);

            // نربط الخطأ بحقل email فيظهر تحته مباشرة في الواجهة.
            // ونستخدم رسالة عامة لا تكشف ما إذا كان البريد مسجّلًا أصلًا.
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // تجديد معرّف الجلسة بعد الدخول يمنع هجمات Session Fixation.
        $this->session()->regenerate();
    }

    /**
     * يتحقق من أن المستخدم لم يتجاوز عدد محاولات الدخول المسموح بها.
     *
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($this->throttleKey()),
            ]),
        ]);
    }

    /**
     * مفتاح العدّاد: البريد + عنوان IP معًا، حتى لا يُحظر مستخدم بريء
     * لمجرد أن شخصًا آخر على نفس الشبكة أخطأ في كلمة المرور.
     */
    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
