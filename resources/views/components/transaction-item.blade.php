@props(['tx', 'compact' => false])
@php $color = $tx->type === 'income' ? 'var(--sq-in)' : 'var(--sq-out)'; @endphp
@if ($compact)
    {{-- dashboard row --}}
    <div class="sq-list-row">
        <span class="sq-icon-box" style="color:{{ $color }};"><x-icon :name="$tx->category->icon" size="18" /></span>
        <div style="flex:1;min-width:0;">
            <div class="sq-truncate" style="font-size:14px;">{{ $tx->category->name }}</div>
            <div class="sq-mute sq-truncate" style="font-size:12px;">
                {{ $tx->date->translatedFormat('j M Y') }}@if($tx->note) · {{ $tx->note }}@endif
            </div>
        </div>
        <div class="sq-num" style="font-size:16px;color:{{ $color }};flex:none;">
            <x-money :amount="$tx->amount" signed :type="$tx->type" />
        </div>
    </div>
@else
    {{-- mobile list card, with its own actions --}}
    <div class="card blueprint" style="padding:14px;gap:10px;">
        <x-corners />
        <div class="sq-row">
            <span class="sq-icon-box" style="width:40px;height:40px;color:{{ $color }};">
                <x-icon :name="$tx->category->icon" size="20" />
            </span>
            <div style="flex:1;min-width:0;">
                <div class="sq-truncate" style="font-size:15px;">{{ $tx->category->name }}</div>
                <div class="sq-mute" style="font-size:12px;">{{ $tx->date->translatedFormat('j M Y') }}</div>
            </div>
            <div class="sq-num" style="font-size:19px;color:{{ $color }};flex:none;">
                <x-money :amount="$tx->amount" signed :type="$tx->type" />
            </div>
        </div>
        @if ($tx->note)
            <div class="sq-mute" style="font-size:13px;">{{ $tx->note }}</div>
        @endif
        <div style="display:flex;gap:8px;padding-top:2px;border-top:1px solid color-mix(in srgb,var(--color-text) 8%,transparent);">
            <a href="{{ route('transactions.edit', $tx) }}" class="btn btn-secondary sq-tap" style="flex:1;min-height:44px;">
                <x-icon name="pencil" size="16" /> {{ __('app.edit') }}
            </a>
            <form id="del-m-{{ $tx->id }}" method="POST" action="{{ route('transactions.destroy', $tx) }}" style="flex:1;">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-secondary sq-tap" style="width:100%;min-height:44px;color:var(--sq-out);"
                        onclick="sqConfirmDelete('del-m-{{ $tx->id }}', @js($tx->category->name))">
                    <x-icon name="trash" size="16" /> {{ __('app.delete') }}
                </button>
            </form>
        </div>
    </div>
@endif
