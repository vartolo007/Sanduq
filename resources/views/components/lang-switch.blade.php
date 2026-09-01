@props(['iconOnly' => false])
@php $other = app()->getLocale() === 'ar' ? 'en' : 'ar'; @endphp
<a href="{{ route('locale.switch', $other) }}"
   class="btn btn-secondary sq-tap @if($iconOnly) btn-icon @endif"
   title="{{ $other === 'ar' ? 'العربية' : 'English' }}"
   aria-label="{{ $other === 'ar' ? 'العربية' : 'English' }}">
    <x-icon name="globe" size="18" />
    @unless ($iconOnly) {{ $other === 'ar' ? 'العربية' : 'English' }} @endunless
</a>
