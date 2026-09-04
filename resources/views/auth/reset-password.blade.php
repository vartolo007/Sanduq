<x-layouts.guest :title="__('app.reset_title')">
    <div class="card blueprint" style="padding:26px 24px;gap:16px;background:var(--color-bg);">
        <x-corners />
        <div>
            <h1 style="margin:0 0 4px;font-family:var(--font-heading);font-size:24px;">{{ __('app.reset_title') }}</h1>
            <p class="sq-mute" style="margin:0;font-size:13px;">{{ __('app.reset_sub') }}</p>
        </div>

        {{-- رسالة "أرسلنا الرمز" تصل من PasswordOtpController بعد إعادة التوجيه --}}
        @if (session('status'))
            <x-alert tone="ok" icon="check">{{ session('status') }}</x-alert>
        @endif

        <form method="POST" action="{{ route('password.store') }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf

            <div class="field">
                <label for="email">{{ __('app.email') }}</label>
                <input id="email" class="input" type="email" name="email" value="{{ old('email', $email) }}"
                       inputmode="email" autocomplete="email" required style="min-height:46px;font-size:15px;">
                @error('email')<div class="sq-field-error"><x-icon name="alert" size="14" /> {{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="code">{{ __('app.otp_code') }}</label>
                {{-- dir=ltr يمنع عكس ترتيب الأرقام في الواجهة العربية.
                     inputmode=numeric يفتح لوحة الأرقام على الجوال. --}}
                <input id="code" class="input sq-num" type="text" name="code" value="{{ old('code') }}"
                       dir="ltr" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code"
                       maxlength="{{ \App\Support\PasswordOtp::LENGTH }}" required autofocus
                       placeholder="000000"
                       style="min-height:52px;font-size:26px;text-align:center;letter-spacing:10px;">
                @error('code')<div class="sq-field-error"><x-icon name="alert" size="14" /> {{ $message }}</div>@enderror
                <div class="sq-mute" style="font-size:12px;">
                    {{ __('app.otp_hint', ['minutes' => \App\Support\PasswordOtp::EXPIRY_MINUTES]) }}
                </div>
            </div>

            <div class="field">
                <label for="password">{{ __('app.new_password') }}</label>
                <input id="password" class="input" type="password" name="password"
                       autocomplete="new-password" required style="min-height:46px;font-size:15px;">
                @error('password')<div class="sq-field-error"><x-icon name="alert" size="14" /> {{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="password_confirmation">{{ __('app.password_confirm') }}</label>
                <input id="password_confirmation" class="input" type="password" name="password_confirmation"
                       autocomplete="new-password" required style="min-height:46px;font-size:15px;">
            </div>

            <button type="submit" class="btn btn-primary btn-block sq-tap" style="min-height:48px;font-size:15px;">
                {{ __('app.reset_cta') }}
            </button>
        </form>

        <div class="sq-mute" style="text-align:center;font-size:13px;display:flex;flex-direction:column;gap:6px;">
            <a href="{{ route('password.request') }}">{{ __('app.resend_code') }}</a>
            <a href="{{ route('login') }}">{{ __('app.back_to_login') }}</a>
        </div>
    </div>
</x-layouts.guest>
