@props(['message', 'icon' => 'check'])
<div class="sq-toast-wrap" role="status" aria-live="polite">
    <div class="sq-toast blueprint" style="max-width:min(460px,100%);">
        <x-corners />
        <span style="flex:none;display:grid;"><x-icon :name="$icon" size="17" /></span>
        <span>{{ $message }}</span>
    </div>
</div>
