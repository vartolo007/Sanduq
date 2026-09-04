<x-layouts.guest :title="__('app.forgot_title')">
    <div class="card blueprint" style="padding:26px 24px;gap:16px;background:var(--color-bg);">
        <x-corners />
        <div>
            <h1 style="margin:0 0 4px;font-family:var(--font-heading);font-size:24px;">{{ __('app.forgot_title') }}</h1>
            <p class="sq-mute" style="margin:0;font-size:13px;">{{ __('app.forgot_sub') }}</p>
        </div>

        {{-- رسالة النجاح تأتي من PasswordOtpController عبر ->with('status', …) --}}
        @if (session('status'))
            <x-alert tone="ok" icon="check">{{ session('status') }}</x-alert>
        @endif

        <form method="POST" action="{{ route('password.email') }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div class="field">
                <label for="email">{{ __('app.email') }}</label>
                <input id="email" class="input" type="email" name="email" value="{{ old('email') }}"
                       inputmode="email" autocomplete="email" required autofocus style="min-height:46px;font-size:15px;">
                @error('email')<div class="sq-field-error"><x-icon name="alert" size="14" /> {{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block sq-tap" style="min-height:48px;font-size:15px;">
                {{ __('app.send_code') }}
            </button>
        </form>

        <div class="sq-mute" style="text-align:center;font-size:13px;display:flex;flex-direction:column;gap:6px;">
            {{-- من وصله رمز سابقًا يدخل مباشرة دون إرسال رمز جديد --}}
            <a href="{{ route('password.reset') }}">{{ __('app.have_code') }}</a>
            <a href="{{ route('login') }}">{{ __('app.back_to_login') }}</a>
        </div>
    </div>
</x-layouts.guest>
