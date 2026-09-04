{{--
    قالب بريد الرمز.

    الأنماط مكتوبة داخل السطور (inline) عمدًا: عملاء البريد يتجاهلون معظم ما
    يأتي في <style>، وبعضهم يحذفه كليًا. والاتجاه يتبع لغة الواجهة وقت الإرسال.
--}}
@php
    $rtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.otp_email_subject') }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f7;">
    <div style="max-width:520px;margin:0 auto;padding:32px 20px;font-family:'Segoe UI',Tahoma,Arial,sans-serif;color:#1a1d21;">

        <div style="background:#ffffff;border:1px solid #e3e6ea;border-radius:14px;padding:32px 28px;">

            <div style="font-size:20px;font-weight:700;margin:0 0 6px;">{{ __('app.brand') }}</div>
            <div style="font-size:15px;font-weight:600;margin:0 0 18px;">{{ __('app.otp_email_title') }}</div>

            <p style="margin:0 0 22px;font-size:14px;line-height:1.7;color:#4a5158;">
                {{ __('app.otp_email_intro') }}
            </p>

            {{-- الرمز: حجم كبير وتباعد حروف واسع ليسهل نسخه أو قراءته من الشاشة.
                 نُبقيه دائمًا يسار-لليمين حتى لا تعكس المتصفحات ترتيب الأرقام. --}}
            <div dir="ltr" style="text-align:center;margin:0 0 22px;">
                <div style="display:inline-block;background:#f4f5f7;border:1px solid #e3e6ea;border-radius:10px;padding:16px 28px;font-size:34px;font-weight:700;letter-spacing:10px;font-family:'Courier New',monospace;">
                    {{ $code }}
                </div>
            </div>

            <p style="margin:0 0 8px;font-size:13px;line-height:1.7;color:#4a5158;">
                {{ __('app.otp_email_expiry', ['minutes' => \App\Support\PasswordOtp::EXPIRY_MINUTES]) }}
            </p>

            <p style="margin:0;font-size:13px;line-height:1.7;color:#8a9199;">
                {{ __('app.otp_email_ignore') }}
            </p>
        </div>

        <div style="text-align:center;font-size:12px;color:#8a9199;margin-top:18px;">
            {{ __('app.brand') }}
        </div>
    </div>
</body>
</html>
