<x-layouts.app :title="__('app.nav_reports')">
    <div class="sq-stack">
        <div>
            <h2 style="margin:0;font-family:var(--font-heading);font-size:26px;">{{ __('app.nav_reports') }}</h2>
            <p class="sq-mute" style="margin:0;font-size:13px;">{{ __('app.reports_sub') }}</p>
        </div>

        {{-- نفس مكوّن الفلترة المستخدم في لوحة التحكم، ويُضاف إليه هنا فلتر
             التصنيف الخاص بالتقارير — البند 3.3.3 --}}
        <x-period-filter :period="$period" route-name="reports.index">
            <form method="GET" action="{{ route('reports.index') }}" class="field" style="max-width:340px;">
                {{-- نحمل الفترة المختارة معنا حتى لا يُعيد تبديل التصنيف ضبطها --}}
                @foreach ($period->queryParams() as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach

                <label for="category">{{ __('app.category') }}</label>
                <select id="category" class="input" name="category_id" style="min-height:44px;" onchange="this.form.submit()">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($selectedCategory?->id === $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </form>
        </x-period-filter>

        <div class="sq-grid sq-g3">
            <x-card pad="18px">
                <div class="sq-label">{{ __('app.total_income') }}</div>
                <div class="sq-stat-num sq-in" style="font-size:28px;"><x-money :amount="$income" /></div>
            </x-card>
            <x-card pad="18px">
                <div class="sq-label">{{ __('app.total_expenses') }}</div>
                <div class="sq-stat-num sq-out" style="font-size:28px;"><x-money :amount="$expense" /></div>
            </x-card>
            <x-card pad="18px">
                <div class="sq-label">{{ __('app.net_balance') }}</div>
                <div class="sq-stat-num" style="font-size:28px;"><x-money :amount="$income - $expense" /></div>
            </x-card>
        </div>

        <x-card>
            <div class="sq-between" style="align-items:center;">
                <h3 style="margin:0;font-family:var(--font-heading);font-size:19px;">
                    {{ __('app.in_out_period', ['period' => $period->label()]) }}
                </h3>
                <x-legend />
            </div>
            <x-chart-bars :series="$series" />
        </x-card>

        <x-card>
            <h3 style="margin:0;font-family:var(--font-heading);font-size:19px;">{{ __('app.by_category') }}</h3>
            @forelse ($byCategory as $cat)
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:13px;">
                        <span class="sq-row" style="gap:8px;min-width:0;">
                            <span style="flex:none;color:var(--color-accent-700);display:grid;">
                                <x-icon :name="$cat['icon']" size="16" />
                            </span>
                            <span class="sq-truncate">{{ $cat['name'] }}</span>
                        </span>
                        <span class="sq-num" style="flex:none;">
                            <x-money :amount="$cat['total']" /> <span class="sq-mute">{{ $cat['pct'] }}%</span>
                        </span>
                    </div>
                    <div class="sq-track"><span style="width:{{ $cat['pct'] }}%;"></span></div>
                </div>
            @empty
                <p class="sq-mute" style="margin:0;font-size:14px;">{{ __('app.no_data') }}</p>
            @endforelse
        </x-card>
    </div>
</x-layouts.app>
