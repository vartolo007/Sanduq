<x-layouts.guest :title="__('app.register')">
    <div class="card blueprint" style="padding:26px 24px;gap:16px;background:var(--color-bg);">
        <x-corners />
        <div>
            <h1 style="margin:0 0 4px;font-family:var(--font-heading);font-size:24px;">{{ __('app.auth_title_reg') }}</h1>
            <p class="sq-mute" style="margin:0;font-size:13px;">{{ __('app.auth_sub_reg') }}</p>
        </div>

        <form method="POST" action="{{ route('register') }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div class="field">
                <label for="name">{{ __('app.full_name') }}</label>
                <input id="name" class="input" type="text" name="name" value="{{ old('name') }}"
                       placeholder="{{ __('app.name_ph') }}" autocomplete="name" required autofocus style="min-height:46px;font-size:15px;">
                @error('name')<div class="sq-field-error"><x-icon name="alert" size="14" /> {{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="email">{{ __('app.email') }}</label>
                <input id="email" class="input" type="email" name="email" value="{{ old('email') }}"
                       inputmode="email" autocomplete="email" required style="min-height:46px;font-size:15px;">
                @error('email')<div class="sq-field-error"><x-icon name="alert" size="14" /> {{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="password">{{ __('app.password') }}</label>
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
                {{ __('app.register') }}
            </button>
        </form>

        <div class="sq-mute" style="text-align:center;font-size:13px;">
            {{ __('app.has_account') }} <a href="{{ route('login') }}">{{ __('app.login') }}</a>
        </div>
    </div>
</x-layouts.guest>
