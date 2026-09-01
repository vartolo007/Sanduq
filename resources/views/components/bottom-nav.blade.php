<x-nav-links />
<nav class="sq-bottom" aria-label="{{ __('app.nav_label') }}">
    @foreach ($items as $item)
        @php $active = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])); @endphp
        <a href="{{ route($item['route']) }}" class="sq-tab sq-tap @if($active) is-active @endif"
           @if($active) aria-current="page" @endif>
            <x-icon :name="$item['icon']" size="20" />
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
