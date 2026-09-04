@php $items = \App\Support\Navigation::items(); @endphp
<aside class="sq-side">
    <a href="{{ route('dashboard') }}" class="sq-brand" style="text-decoration:none;color:inherit;">
        <span class="sq-mark">ص</span>
        <span class="sq-rail-label">
            <span style="display:block;font-family:var(--font-heading);font-size:19px;line-height:1;">{{ __('app.brand') }}</span>
            <span class="sq-label" style="font-size:9px;">{{ __('app.brand_sub') }}</span>
        </span>
    </a>

    <nav aria-label="{{ __('app.nav_label') }}" style="display:flex;flex-direction:column;gap:4px;">
        @foreach ($items as $item)
            @php $active = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])); @endphp
            <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
               class="sq-nav-item sq-tap @if($active) is-active @endif"
               @if($active) aria-current="page" @endif>
                <x-icon :name="$item['icon']" size="20" />
                <span class="sq-rail-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div style="flex:1;"></div>

    <a href="{{ route('transactions.create') }}" class="btn btn-primary blueprint sq-tap"
       style="justify-content:center;gap:8px;min-height:42px;margin-bottom:10px;">
        <x-corners />
        <x-icon name="plus" size="18" />
        <span class="sq-rail-label">{{ __('app.add_tx') }}</span>
    </a>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="sq-nav-item sq-tap" style="cursor:pointer;">
            <x-icon name="logout" size="18" />
            <span class="sq-rail-label">{{ __('app.logout') }}</span>
        </button>
    </form>
</aside>
