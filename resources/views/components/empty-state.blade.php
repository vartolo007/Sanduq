@props(['title', 'body', 'cta' => null, 'href' => null, 'icon' => 'receipt'])
<div class="card blueprint sq-empty">
    <x-corners />
    <span class="sq-icon-box" style="width:56px;height:56px;color:var(--color-accent);">
        <x-icon :name="$icon" size="24" />
    </span>
    <h2 style="margin:0;font-family:var(--font-heading);font-size:22px;">{{ $title }}</h2>
    <p class="sq-mute" style="margin:0;max-width:38ch;font-size:14px;">{{ $body }}</p>
    @if ($cta && $href)
        <a href="{{ $href }}" class="btn btn-primary sq-tap" style="min-height:44px;">
            <x-icon name="plus" size="18" /> {{ $cta }}
        </a>
    @endif
</div>
