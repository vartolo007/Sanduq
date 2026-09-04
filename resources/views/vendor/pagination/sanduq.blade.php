{{-- ترقيم الصفحات بأسلوب التطبيق. القالب الافتراضي في Laravel مكتوب بـ Tailwind
     وهو غير محمّل في هذا المشروع، فبنينا بديلًا يستخدم أصناف sanduq.css.

     نستخدم روابط منطقية (start/end) بدل يمين/يسار حتى ينقلب الترتيب تلقائيًا
     مع اتجاه الصفحة. --}}

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('app.pagination') }}"
         style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;justify-content:center;">

        {{-- السابق --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-secondary" style="min-height:40px;opacity:.45;pointer-events:none;">
                {{ __('app.prev') }}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="btn btn-secondary sq-tap" style="min-height:40px;">{{ __('app.prev') }}</a>
        @endif

        {{-- أرقام الصفحات --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="sq-mute" style="padding:0 6px;">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="btn btn-primary sq-num" aria-current="page"
                              style="min-height:40px;min-width:40px;justify-content:center;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="btn btn-secondary sq-tap sq-num"
                           style="min-height:40px;min-width:40px;justify-content:center;">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- التالي --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="btn btn-secondary sq-tap" style="min-height:40px;">{{ __('app.next') }}</a>
        @else
            <span class="btn btn-secondary" style="min-height:40px;opacity:.45;pointer-events:none;">
                {{ __('app.next') }}
            </span>
        @endif
    </nav>
@endif
