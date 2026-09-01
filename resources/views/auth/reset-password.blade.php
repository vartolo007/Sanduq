<x-layouts.guest :title="__('app.reset_title')">
    <div class="card blueprint" style="padding:26px 24px;gap:16px;background:var(--color-bg);">
        <x-corners />
        <div>
            <h1 style="margin:0 0 4px;font-family:var(--font-heading);font-size:24px;">{{ __('app.reset_title') }}</h1>
            <p class="sq-mute" style="margin:0;font-size:13px;">{{ __('app.reset_sub') }}</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf

            {{-- الرمز يأتي من الرابط المُرسل بالبريد ويعود مع النموذج ليتحقق منه الخادم --}}
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="field">
                <label for="email">{{ __('app.email') }}</label>
                <input id="email" class="input" type="email" name="email" value="{{ old('email', $email) }}"
                       inputmode="email" autocomplete="email" required style="min-height:46px;font-size:15px;">
                @error('email')<div class="sq-field-error"><x-icon name="alert" size="14" /> {{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="password">{{ __('app.new_password') }}</label>
                <input id="password" class="input" type="password" name="password"
                       autocomplete="new-password" required autofocus style="min-height:46px;font-size:15px;">
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

        <div class="sq-mute" style="text-align:center;font-size:13px;">
            <a href="{{ route('login') }}">{{ __('app.back_to_login') }}</a>
        </div>
    </div>
</x-layouts.guest>
