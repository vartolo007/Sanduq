@props(['pad' => '20px', 'lift' => false])
<section {{ $attributes->merge(['class' => 'card blueprint' . ($lift ? ' sq-lift' : '')]) }}
         style="padding:{{ $pad }};gap:14px;">
    <x-corners />
    {{ $slot }}
</section>
