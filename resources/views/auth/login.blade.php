<x-layouts.guest :title="__('app.login')">
    <div class="card blueprint" style="padding:26px 24px;gap:16px;background:var(--color-bg);">
        <x-corners />
        <div>
            <h1 style="margin:0 0 4px;font-family:var(--font-heading);font-size:24px;">{{ __('app.auth_title_login') }}</h1>
            <p class="sq-mute" style="margin:0;font-size:13px;">{{ __('app.auth_sub_login') }}</p>
        </div>

        {{-- رسائل تصل إلى هذه الصفحة بعد إجراء تم في مكان آخر: تغيير كلمة
             المرور بنجاح، أو حذف الحساب. بدون هذا القسم تضيع الرسالة صامتة. --}}
        @if (session('status'))
            <x-alert tone="ok" icon="check">{{ session('status') }}</x-alert>
        @endif

        @if ($errors->any())
            <x-alert tone="error">{{ __('app.err_bad_login') }}</x-alert>
        @endif

        <form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div class="field">
                <label for="email">{{ __('app.email') }}</label>
                <input id="email" class="input" type="email" name="email" value="{{ old('email') }}"
                       inputmode="email" autocomplete="email" required autofocus style="min-height:46px;font-size:15px;">
            </div>
            <div class="field">
                <label for="password">{{ __('app.password') }}</label>
                <input id="password" class="input" type="password" name="password"
                       autocomplete="current-password" required style="min-height:46px;font-size:15px;">
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;font-size:13px;">
                <label class="radio">
                    <input type="checkbox" name="remember"><span class="dot" style="border-radius:0;"></span>{{ __('app.remember') }}
                </label>
                <a href="{{ route('password.request') }}">{{ __('app.forgot') }}</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block sq-tap" style="min-height:48px;font-size:15px;">
                {{ __('app.login') }}
            </button>
        </form>

        <div class="sq-mute" style="text-align:center;font-size:13px;">
            {{ __('app.no_account') }} <a href="{{ route('register') }}">{{ __('app.register') }}</a>
        </div>
    </div>
</x-layouts.guest>
