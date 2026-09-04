<x-layouts.app :title="__('app.nav_profile')">
    <div class="sq-stack" style="max-width:760px;">
        <div>
            <h2 style="margin:0;font-family:var(--font-heading);font-size:26px;">{{ __('app.nav_profile') }}</h2>
            <p class="sq-mute" style="margin:0;font-size:13px;">{{ __('app.profile_sub') }}</p>
        </div>

        <x-card>
            <div class="sq-row" style="gap:14px;">
                <span class="sq-icon-box" style="width:56px;height:56px;font-family:var(--font-heading);font-size:22px;">
                    {{ auth()->user()->initials() }}
                </span>
                <div style="min-width:0;">
                    <div style="font-family:var(--font-heading);font-size:20px;">{{ auth()->user()->name ?? __('app.user_name') }}</div>
                    <div class="sq-mute" style="font-size:13px;">{{ auth()->user()->email ?? '' }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="sq-stack" style="--sq-gap:12px;">
                @csrf @method('PATCH')
                <div class="sq-grid sq-g2" style="gap:12px;">
                    <div class="field">
                        <label for="p-name">{{ __('app.full_name') }}</label>
                        <input id="p-name" class="input" type="text" name="name"
                               value="{{ old('name', auth()->user()->name ?? '') }}" style="min-height:44px;">
                        @error('name')<div class="sq-field-error"><x-icon name="alert" size="14" /> {{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label for="p-email">{{ __('app.email') }}</label>
                        <input id="p-email" class="input" type="email" name="email"
                               value="{{ old('email', auth()->user()->email ?? '') }}" style="min-height:44px;">
                        @error('email')<div class="sq-field-error"><x-icon name="alert" size="14" /> {{ $message }}</div>@enderror
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary sq-tap" style="min-height:42px;">{{ __('app.save_changes') }}</button>
                </div>
            </form>
        </x-card>

        <x-card>
            <h3 style="margin:0;font-family:var(--font-heading);font-size:19px;">{{ __('app.preferences') }}</h3>

            <div class="sq-between" style="align-items:center;">
                <div>
                    <div style="font-size:14px;">{{ __('app.language') }}</div>
                    <div class="sq-mute" style="font-size:12px;">{{ __('app.language_hint') }}</div>
                </div>
                <div class="sq-seg" style="flex:none;">
                    @foreach (['ar' => 'العربية', 'en' => 'English'] as $code => $label)
                        <a href="{{ route('locale.switch', $code) }}"
                           class="sq-seg-opt sq-tap @if(app()->getLocale() === $code) is-on @endif">{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            <div style="height:1px;background:var(--color-divider);"></div>

            <div class="sq-between" style="align-items:center;">
                <div>
                    <div style="font-size:14px;">{{ __('app.appearance') }}</div>
                    <div class="sq-mute" style="font-size:12px;">{{ __('app.appearance_hint') }}</div>
                </div>
                <button type="button" class="btn btn-secondary sq-tap" onclick="sqToggleTheme()" style="min-height:42px;flex:none;">
                    <x-icon name="moon" size="18" /> {{ __('app.theme_toggle') }}
                </button>
            </div>

            <div style="height:1px;background:var(--color-divider);"></div>

            <div class="sq-between" style="align-items:center;">
                <div>
                    <div style="font-size:14px;">{{ __('app.currency') }}</div>
                    <div class="sq-mute" style="font-size:12px;">{{ __('app.currency_hint') }}</div>
                </div>
                <span class="tag tag-accent" style="flex:none;">{{ __('app.currency_value') }}</span>
            </div>
        </x-card>

        <x-card style="border-color:color-mix(in srgb,var(--sq-out) 45%,transparent);">
            <h3 style="margin:0;font-family:var(--font-heading);font-size:19px;color:var(--sq-out);">{{ __('app.account') }}</h3>
            <p class="sq-mute" style="margin:0;font-size:13px;">{{ __('app.account_copy') }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary sq-tap" style="min-height:44px;">
                    <x-icon name="logout" size="18" /> {{ __('app.logout') }}
                </button>
            </form>

            <div style="height:1px;background:var(--color-divider);"></div>

            {{-- حذف الحساب لا رجعة فيه، فنطلب كلمة المرور قبله حتى لا يكفي
                 الوصول إلى جهاز مفتوح لمحو كل السجلات. --}}
            <form id="del-account" method="POST" action="{{ route('profile.destroy') }}"
                  style="display:flex;flex-direction:column;gap:10px;">
                @csrf @method('DELETE')

                <p class="sq-mute" style="margin:0;font-size:13px;">{{ __('app.delete_account_hint') }}</p>

                <div class="field" style="max-width:320px;">
                    <label for="del-pass">{{ __('app.confirm_password') }}</label>
                    <input id="del-pass" class="input" type="password" name="password"
                           autocomplete="current-password" style="min-height:44px;">
                    @error('password')<div class="sq-field-error"><x-icon name="alert" size="14" /> {{ $message }}</div>@enderror
                </div>

                <div>
                    <button type="button" class="btn btn-secondary sq-tap" style="min-height:44px;color:var(--sq-out);"
                            onclick="sqConfirmDelete('del-account')">
                        <x-icon name="trash" size="18" /> {{ __('app.delete_account') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
