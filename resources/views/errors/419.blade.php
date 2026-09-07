{{-- انتهاء صلاحية رمز CSRF — تحدث إذا بقيت صفحة نموذج مفتوحة حتى انتهت
     الجلسة. رسالة Laravel الافتراضية "Page Expired" لا تدلّ على الحل. --}}
<x-layouts.guest :title="__('app.expired_title')">
    <div class="card blueprint" style="padding:32px 24px;gap:14px;align-items:center;text-align:center;">
        <x-corners />
        <span class="sq-icon-box" style="width:52px;height:52px;">
            <x-icon name="alert" size="22" />
        </span>
        <h1 style="margin:0;font-family:var(--font-heading);font-size:24px;">{{ __('app.expired_title') }}</h1>
        <p class="sq-mute" style="margin:0;font-size:14px;max-width:40ch;">{{ __('app.expired_body') }}</p>
        <a href="{{ url('/') }}" class="btn btn-primary sq-tap" style="min-height:44px;">{{ __('app.back_home') }}</a>
    </div>
</x-layouts.guest>
