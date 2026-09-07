{{-- تظهر عند رفض السياسات (TransactionPolicy / CategoryPolicy) وصولًا لسجل
     لا يخصّ المستخدم. تحلّ محلّ صفحة Laravel الافتراضية "This action is
     unauthorized." التي لا تشرح للمستخدم ما عليه فعله. --}}
<x-layouts.guest :title="__('app.forbidden_title')">
    <div class="card blueprint" style="padding:32px 24px;gap:14px;align-items:center;text-align:center;">
        <x-corners />
        <span class="sq-icon-box" style="width:52px;height:52px;color:var(--sq-out);border-color:var(--sq-out);">
            <x-icon name="alert" size="22" />
        </span>
        <h1 style="margin:0;font-family:var(--font-heading);font-size:24px;">{{ __('app.forbidden_title') }}</h1>
        <p class="sq-mute" style="margin:0;font-size:14px;max-width:40ch;">{{ __('app.forbidden_body') }}</p>
        <a href="{{ url('/') }}" class="btn btn-primary sq-tap" style="min-height:44px;">{{ __('app.back_home') }}</a>
    </div>
</x-layouts.guest>
