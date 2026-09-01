<x-layouts.app :title="__('app.nav_home')">
    <div class="sq-stack">

        <div class="sq-between">
            <div>
                <div class="sq-kicker">{{ now()->translatedFormat('j F Y') }}</div>
                <h2 style="margin:2px 0;font-family:var(--font-heading);font-size:28px;">
                    {{ __('app.greeting') }}{{ explode(' ', auth()->user()->name ?? __('app.user_name'))[0] }}
                </h2>
                <p class="sq-mute" style="margin:0;font-size:14px;">{{ __('app.dash_sub') }}</p>
            </div>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary sq-tap sq-only-desk" style="min-height:40px;">
                <x-icon name="chart" size="17" /> {{ __('app.view_reports') }}
            </a>
        </div>

        <div class="sq-grid sq-dash" style="align-items:start;">
            <div class="sq-stack" style="min-width:0;">
                <x-balance-card
                    :balance="view('components.money', ['amount' => $income - $expense])->render()"
                    :income-share="$income + $expense > 0 ? round($income / ($income + $expense) * 100) : 50"
                    :savings="$income > 0 ? round(($income - $expense) / $income * 100) . '%' : '0%'" />

                <div class="sq-grid sq-g2">
                    <x-stat-card tone="income" :label="__('app.total_income')"
                        :value="view('components.money', ['amount' => $income])->render()"
                        :meta="$incomeCount . ' ' . __('app.tx_count')" />
                    <x-stat-card tone="expense" :label="__('app.total_expenses')"
                        :value="view('components.money', ['amount' => $expense])->render()"
                        :meta="$expenseCount . ' ' . __('app.tx_count')" />
                </div>

                <x-card>
                    <div class="sq-between" style="align-items:center;">
                        <div>
                            <div class="sq-kicker">{{ __('app.overview') }}</div>
                            <h3 style="margin:0;font-family:var(--font-heading);font-size:19px;">{{ __('app.six_months') }}</h3>
                        </div>
                        <x-legend />
                    </div>
                    <x-chart-bars :series="$series" />
                </x-card>
            </div>

            <div class="sq-stack" style="min-width:0;">
                <x-card>
                    <h3 style="margin:0;font-family:var(--font-heading);font-size:19px;">{{ __('app.top_categories') }}</h3>
                    @foreach ($topCategories as $cat)
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:13px;">
                                <span class="sq-row" style="gap:8px;min-width:0;">
                                    <span style="flex:none;color:var(--color-accent-700);display:grid;">
                                        <x-icon :name="$cat['icon']" size="16" />
                                    </span>
                                    <span class="sq-truncate">{{ $cat['name'] }}</span>
                                </span>
                                <span class="sq-num" style="flex:none;"><x-money :amount="$cat['total']" /></span>
                            </div>
                            <div class="sq-track"><span style="width:{{ $cat['pct'] }}%;"></span></div>
                        </div>
                    @endforeach
                </x-card>
            </div>
        </div>

        <section style="display:flex;flex-direction:column;gap:12px;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <h3 style="margin:0;font-family:var(--font-heading);font-size:19px;">{{ __('app.recent') }}</h3>
                <a href="{{ route('transactions.index') }}" class="btn btn-ghost sq-tap">
                    {{ __('app.see_all') }} <x-icon name="chevron" size="15" />
                </a>
            </div>
            @if ($recent->isEmpty())
                <x-empty-state :title="__('app.empty_title')" :body="__('app.empty_copy')"
                               :cta="__('app.empty_cta')" :href="route('transactions.create')" />
            @else
                <div class="card blueprint sq-list">
                    <x-corners />
                    @foreach ($recent as $tx)
                        <x-transaction-item :tx="$tx" compact />
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
