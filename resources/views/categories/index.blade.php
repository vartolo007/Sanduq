<x-layouts.app :title="__('app.nav_categories')">
    <div class="sq-stack">
        <div class="sq-between">
            <div>
                <h2 style="margin:0;font-family:var(--font-heading);font-size:26px;">{{ __('app.nav_categories') }}</h2>
                <p class="sq-mute" style="margin:0;font-size:13px;">{{ __('app.categories_sub') }}</p>
            </div>
            <button type="button" class="btn btn-primary sq-tap" data-sq-open="sq-cat-sheet" style="min-height:42px;">
                <x-icon name="plus" size="18" /> {{ __('app.new_category') }}
            </button>
        </div>

        @foreach (['income', 'expense'] as $type)
            @php $list = $categories->where('type', $type); $color = $type === 'income' ? 'var(--sq-in)' : 'var(--sq-out)'; @endphp
            <section style="display:flex;flex-direction:column;gap:12px;">
                <div class="sq-row" style="gap:10px;">
                    <span class="sq-icon-box" style="width:22px;height:22px;color:{{ $color }};border-color:color-mix(in srgb,{{ $color }} 45%,transparent);">
                        <x-icon :name="$type === 'income' ? 'up' : 'down'" size="14" />
                    </span>
                    <h3 style="margin:0;font-family:var(--font-heading);font-size:19px;">
                        {{ $type === 'income' ? __('app.income_categories') : __('app.expense_categories') }}
                    </h3>
                    <span class="tag tag-neutral">{{ $list->count() }}</span>
                </div>

                <div class="sq-grid sq-g4" style="gap:12px;">
                    @foreach ($list as $cat)
                        <div class="card blueprint sq-lift" style="padding:16px;gap:10px;">
                            <x-corners />
                            <span class="sq-icon-box" style="width:36px;height:36px;color:{{ $color }};">
                                <x-icon :name="$cat->icon" size="20" />
                            </span>
                            <div style="font-family:var(--font-heading);font-size:17px;">{{ $cat->name }}</div>
                            <div class="sq-mute" style="font-size:12px;">
                                {{ $cat->transactions_count }} {{ __('app.tx_count') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    {{-- bottom sheet on mobile, centred dialog on desktop — one component, one markup --}}
    <div id="sq-cat-sheet" class="sq-sheet" hidden>
        <form method="POST" action="{{ route('categories.store') }}" class="sq-sheet-panel blueprint">
            @csrf
            <x-corners />
            <div class="sq-sheet-head">
                <h2 style="margin:0;flex:1;font-family:var(--font-heading);font-size:19px;">{{ __('app.new_category') }}</h2>
                <button type="button" class="btn btn-secondary btn-icon sq-tap" data-sq-close aria-label="{{ __('app.close') }}">
                    <x-icon name="x" size="17" />
                </button>
            </div>
            <div class="sq-sheet-body">
                <div class="field">
                    <label for="cat-name">{{ __('app.category_name') }}</label>
                    <input id="cat-name" class="input" type="text" name="name" required style="min-height:48px;font-size:15px;">
                </div>
                <div>
                    <div class="sq-mute" style="font-size:12px;margin-bottom:6px;">{{ __('app.type') }}</div>
                    <div class="sq-seg" style="width:100%;">
                        <label class="sq-seg-opt" style="flex:1;">
                            <input type="radio" name="type" value="income" checked style="margin-inline-end:6px;">
                            {{ __('app.income') }}
                        </label>
                        <label class="sq-seg-opt" style="flex:1;">
                            <input type="radio" name="type" value="expense" style="margin-inline-end:6px;">
                            {{ __('app.expense') }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="sq-sheet-foot">
                <button type="button" class="btn btn-secondary sq-tap sq-only-desk" data-sq-close style="min-height:48px;flex:1;">
                    {{ __('app.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary sq-tap" style="min-height:48px;flex:2;">
                    <x-icon name="check" size="18" /> {{ __('app.save') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
