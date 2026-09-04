<?php

namespace App\Support;

use App\Mail\PasswordOtpMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * رمز استعادة كلمة المرور المكوّن من ٦ خانات.
 *
 * الرمز قصير جدًا مقارنةً برمز الرابط الذي يولّده Laravel افتراضيًا: مليون
 * احتمال فقط مقابل رمز عشوائي بطول ٦٠ حرفًا. لذلك لا يقوم أمانه على طوله بل
 * على ثلاثة قيود مجتمعة، وإسقاط أيٍّ منها يجعل تخمينه آليًا مسألة دقائق:
 *
 *   1. صلاحية قصيرة — ١٠ دقائق
 *   2. سقف للمحاولات الخاطئة — ٥ محاولات ثم إغلاق مؤقت
 *   3. سقف لتكرار الإرسال — طلب واحد كل ٦٠ ثانية
 *
 * الرمز نفسه لا يُخزَّن أبدًا كنص صريح، بل مُجزَّأ (hashed) في جدول
 * password_reset_tokens نفسه الذي يستخدمه Laravel.
 */
final class PasswordOtp
{
    /** عدد خانات الرمز كما يظهر للمستخدم. */
    public const LENGTH = 6;

    /** مدة صلاحية الرمز بالدقائق. */
    public const EXPIRY_MINUTES = 10;

    /** أقصى عدد محاولات خاطئة قبل الإغلاق المؤقت. */
    public const MAX_ATTEMPTS = 5;

    /** مدة الإغلاق بعد استنفاد المحاولات، بالثواني. */
    private const LOCKOUT_SECONDS = 900;

    /** أقل فاصل زمني بين طلبَي إرسال، بالثواني. */
    private const RESEND_SECONDS = 60;

    /**
     * هل يُسمح بإرسال رمز جديد لهذا البريد الآن؟
     */
    public static function canSend(string $email): bool
    {
        return ! RateLimiter::tooManyAttempts(self::sendKey($email), 1);
    }

    /**
     * الثواني المتبقية قبل السماح بإرسال جديد.
     */
    public static function secondsUntilResend(string $email): int
    {
        return RateLimiter::availableIn(self::sendKey($email));
    }

    /**
     * يولّد رمزًا جديدًا ويخزّنه مجزّأً ثم يرسله على البريد.
     *
     * الإرسال متزامن (بلا طابور) لأن المشروع لا يشغّل queue worker؛ الطلب
     * ينتظر اتصال SMTP وهو مقبول عند هذا الحجم.
     */
    public static function send(string $email): void
    {
        // random_int مولّد آمن تشفيريًا، بعكس rand/mt_rand التي يمكن التنبؤ بها.
        $code = str_pad((string) random_int(0, 999999), self::LENGTH, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($code), 'created_at' => now()],
        );

        // رمز جديد يعني بداية نظيفة: نصفّر عدّاد المحاولات الخاطئة حتى لا
        // يبقى المستخدم مغلقًا بسبب محاولاته على رمز قديم.
        RateLimiter::clear(self::attemptKey($email));

        RateLimiter::hit(self::sendKey($email), self::RESEND_SECONDS);

        Mail::to($email)->send(new PasswordOtpMail($code));
    }

    /**
     * هل استُنفدت محاولات هذا البريد؟
     */
    public static function tooManyAttempts(string $email): bool
    {
        return RateLimiter::tooManyAttempts(self::attemptKey($email), self::MAX_ATTEMPTS);
    }

    /**
     * الثواني المتبقية على انتهاء الإغلاق.
     */
    public static function secondsUntilRetry(string $email): int
    {
        return RateLimiter::availableIn(self::attemptKey($email));
    }

    /**
     * يتحقق من الرمز: موجود، غير منتهٍ، ومطابق.
     *
     * كل محاولة خاطئة تُسجَّل في عدّاد المحاولات. النجاح لا يحذف الرمز — يترك
     * ذلك للمنادي بعد أن يغيّر كلمة المرور فعلًا، عبر clear().
     */
    public static function verify(string $email, string $code): bool
    {
        $row = DB::table('password_reset_tokens')->where('email', $email)->first();

        if ($row === null) {
            RateLimiter::hit(self::attemptKey($email), self::LOCKOUT_SECONDS);

            return false;
        }

        if (Carbon::parse($row->created_at)->addMinutes(self::EXPIRY_MINUTES)->isPast()) {
            self::clear($email);

            return false;
        }

        // Hash::check تقارن في زمن ثابت، فلا تكشف مقدار التطابق عبر توقيت الرد.
        if (! Hash::check($code, $row->token)) {
            RateLimiter::hit(self::attemptKey($email), self::LOCKOUT_SECONDS);

            return false;
        }

        return true;
    }

    /**
     * يُبطل الرمز الحالي ويصفّر العدّادات — يُستدعى بعد نجاح التغيير.
     */
    public static function clear(string $email): void
    {
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        RateLimiter::clear(self::attemptKey($email));
    }

    private static function attemptKey(string $email): string
    {
        return 'pw-otp-verify:'.$email;
    }

    private static function sendKey(string $email): string
    {
        return 'pw-otp-send:'.$email;
    }
}
