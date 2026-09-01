<x-layouts.app :title="__('app.nav_transactions')">
    <div class="sq-stack">
        <div class="sq-between">
            <div>
                <h2 style="margin:0;font-family:var(--font-heading);font-size:26px;">{{ __('app.nav_transactions') }}</h2>
                <p class="sq-mute" style="margin:0;font-size:13px;">
                    {{ $transactions->total() }} {{ __('app.tx_count') }}
                    @if (request('q')) · “{{ request('q') }}” @endif
                </p>
            </div>
            <a href="{{ route('transactions.create') }}" class="btn btn-primary sq-tap sq-only-desk" style="min-height:42px;">
                <x-icon name="plus" size="18" /> {{ __('app.add_tx') }}
            </a>
        </div>

        {{-- filters: links, so the state lives in the URL and survives a refresh --}}
        <form method="GET" action="{{ route('transactions.index') }}"
              style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
            <div class="sq-seg">
                @foreach (['all' => __('app.all'), 'income' => __('app.income'), 'expense' => __('app.expense')] as $key => $label)
                    <a href="{{ route('transactions.index', array_merge(request()->except('page'), ['type' => $key])) }}"
                       class="sq-seg-opt sq-tap @if(request('type', 'all') === $key) is-on @endif"
                       @if(request('type', 'all') === $key) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </div>
            <label class="sq-search sq-only-mobile" style="flex:1;min-width:180px;min-height:42px;">
                <span class="sq-mute" style="display:grid;flex:none;"><x-icon name="search" size="17" /></span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('app.search_ph') }}"
                       style="font-size:15px;" aria-label="{{ __('app.search_ph') }}">
            </label>
        </form>

        @if ($transactions->isEmpty())
            <x-empty-state :title="__('app.empty_title')" :body="__('app.empty_copy')"
                           :cta="__('app.empty_cta')" :href="route('transactions.create')" />
        @else
            <x-transaction-table :transactions="$transactions" />

            <div class="sq-only-mobile" style="display:flex;flex-direction:column;gap:10px;">
                @foreach ($transactions as $tx)
                    <x-transaction-item :tx="$tx" />
                @endforeach
            </div>

            <div>{{ $transactions->withQueryString()->links() }}</div>
        @endif
    </div>
</x-layouts.app>
