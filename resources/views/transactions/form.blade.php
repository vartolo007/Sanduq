@php
    $editing = isset($transaction);
    $type = old('type', $editing ? $transaction->type : 'expense');
@endphp
<x-layouts.app :title="$editing ? __('app.edit_tx') : __('app.add_tx')">
    <div style="max-width:560px;">
        <form method="POST" action="{{ $editing ? route('transactions.update', $transaction) : route('transactions.store') }}"
              class="card blueprint" style="padding:0;gap:0;">
            @csrf
            @if ($editing) @method('PUT') @endif
            <x-corners />

            <div class="sq-sheet-head" style="position:static;">
                <h2 style="margin:0;flex:1;font-family:var(--font-heading);font-size:20px;">
                    {{ $editing ? __('app.edit_tx') : __('app.add_tx') }}
                </h2>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-icon sq-tap" aria-label="{{ __('app.close') }}">
                    <x-icon name="x" size="17" />
                </a>
            </div>

            <div class="sq-sheet-body">
                {{-- type: drives which categories are selectable --}}
                <div>
                    <div class="sq-mute" style="font-size:12px;margin-bottom:6px;">{{ __('app.type') }}</div>
                    <input type="hidden" name="type" id="sq-type-value" value="{{ $type }}">
                    <div class="sq-type">
                        <button type="button" value="income" class="sq-type-opt sq-tap @if($type === 'income') is-on-in @endif"
                                aria-pressed="{{ $type === 'income' ? 'true' : 'false' }}" onclick="sqSyncCategories('income')">
                            <x-icon name="up" size="18" /> {{ __('app.income') }}
                        </button>
                        <button type="button" value="expense" class="sq-type-opt sq-tap @if($type === 'expense') is-on-out @endif"
                                aria-pressed="{{ $type === 'expense' ? 'true' : 'false' }}" onclick="sqSyncCategories('expense')">
                            <x-icon name="down" size="18" /> {{ __('app.expense') }}
                        </button>
                    </div>
                </div>

                {{-- amount: the loudest field on the screen --}}
                <div>
                    <div class="sq-mute" style="font-size:12px;margin-bottom:6px;">{{ __('app.amount') }}</div>
                    <div class="sq-amount @error('amount') has-error @enderror">
                        <span class="sq-num sq-mute" style="font-size:18px;flex:none;">{{ __('app.currency_short') }}</span>
                        <input type="text" name="amount" inputmode="decimal" placeholder="0.00"
                               value="{{ old('amount', $editing ? $transaction->amount : '') }}"
                               aria-label="{{ __('app.amount') }}" required>
                    </div>
                    @error('amount')<div class="sq-field-error"><x-icon name="alert" size="14" /> {{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="sq-category">{{ __('app.category') }}</label>
                    <select id="sq-category" class="input" name="category_id" required style="min-height:48px;font-size:15px;">
                        <option value="">{{ __('app.choose') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" data-type="{{ $cat->type }}"
                                    @if($cat->type !== $type) hidden @endif
                                    @selected(old('category_id', $editing ? $transaction->category_id : null) === $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="sq-field-error"><x-icon name="alert" size="14" /> {{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="sq-date">{{ __('app.date') }}</label>
                    <input id="sq-date" class="input" type="date" name="date" required style="min-height:48px;font-size:15px;"
                           value="{{ old('date', $editing ? $transaction->date->format('Y-m-d') : now()->format('Y-m-d')) }}">
                </div>

                <div class="field">
                    <label for="sq-note">{{ __('app.note') }}</label>
                    <textarea id="sq-note" class="input" name="note" rows="3" placeholder="{{ __('app.note_ph') }}"
                              style="font-size:15px;">{{ old('note', $editing ? $transaction->note : '') }}</textarea>
                </div>
            </div>

            <div class="sq-sheet-foot" style="position:static;">
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary sq-tap sq-only-desk"
                   style="min-height:48px;flex:1;justify-content:center;">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn btn-primary sq-tap" style="min-height:48px;flex:2;font-size:15px;">
                    <x-icon name="check" size="18" /> {{ __('app.save') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
