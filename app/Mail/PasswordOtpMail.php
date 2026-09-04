<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * رسالة رمز استعادة كلمة المرور.
 *
 * الرمز يُمرَّر نصًا صريحًا هنا لأنه لحظة الإرسال فقط؛ النسخة المخزّنة في
 * قاعدة البيانات مجزّأة (انظر PasswordOtp).
 */
class PasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $code)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('app.otp_email_subject'));
    }

    public function content(): Content
    {
        // $code متاح في القالب تلقائيًا لأنه خاصية عامة في هذا الصنف.
        return new Content(view: 'emails.password-otp');
    }
}
