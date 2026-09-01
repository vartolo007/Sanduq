@props(['transactions'])
{{-- Desktop only. Mobile uses x-transaction-item instead — never this table. --}}
<div class="card blueprint sq-only-desk" style="padding:0;gap:0;">
    <x-corners />
    <table class="table">
        <thead>
            <tr>
                <th style="text-align:start;">{{ __('app.col_date') }}</th>
                <th style="text-align:start;">{{ __('app.col_category') }}</th>
                <th style="text-align:start;">{{ __('app.col_type') }}</th>
                <th style="text-align:start;">{{ __('app.col_note') }}</th>
                <th style="text-align:end;">{{ __('app.col_amount') }}</th>
                <th style="text-align:end;">{{ __('app.col_actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($transactions as $tx)
            @php $color = $tx->type === 'income' ? 'var(--sq-in)' : 'var(--sq-out)'; @endphp
            <tr>
                <td class="sq-num" style="white-space:nowrap;">{{ $tx->date->translatedFormat('j M Y') }}</td>
                <td>
                    <span class="sq-row" style="gap:10px;">
                        <span style="color:var(--color-accent-700);display:grid;flex:none;">
                            <x-icon :name="$tx->category->icon" size="18" />
                        </span>
                        {{ $tx->category->name }}
                    </span>
                </td>
                <td>
                    <span class="tag" style="border:1px solid {{ $color }};color:{{ $color }};background:transparent;">
                        {{ $tx->type === 'income' ? __('app.income') : __('app.expense') }}
                    </span>
                </td>
                <td class="sq-mute" style="max-width:280px;">{{ $tx->note ?: '—' }}</td>
                <td class="sq-num" style="text-align:end;white-space:nowrap;font-size:16px;color:{{ $color }};">
                    <x-money :amount="$tx->amount" signed :type="$tx->type" />
                </td>
                <td style="text-align:end;white-space:nowrap;">
                    <span style="display:inline-flex;gap:6px;">
                        <a href="{{ route('transactions.edit', $tx) }}" class="btn btn-secondary btn-icon sq-tap"
                           title="{{ __('app.edit') }}" aria-label="{{ __('app.edit') }}">
                            <x-icon name="pencil" size="16" />
                        </a>
                        <form id="del-d-{{ $tx->id }}" method="POST" action="{{ route('transactions.destroy', $tx) }}">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-secondary btn-icon sq-tap"
                                    title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"
                                    onclick="sqConfirmDelete('del-d-{{ $tx->id }}', @js($tx->category->name))">
                                <x-icon name="trash" size="16" />
                            </button>
                        </form>
                    </span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
