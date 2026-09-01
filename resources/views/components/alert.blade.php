@props(['tone' => 'error', 'icon' => 'alert'])
<div role="alert" class="sq-alert sq-alert-{{ $tone }}">
    <span style="flex:none;line-height:1.4;color:{{ $tone === 'error' ? 'var(--sq-out)' : ($tone === 'warn' ? 'var(--sq-warn)' : 'var(--sq-in)') }};">
        <x-icon :name="$icon" size="15" />
    </span>
    <span>{{ $slot }}</span>
</div>
