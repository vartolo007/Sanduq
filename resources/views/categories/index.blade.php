<x-layouts.app :title="__('app.nav_categories')">
    @php
        // بعد فشل تحقّق التعديل يعود editing_id مع old()، فنعيد فتح النافذة على
        // وضع التعديل (المسار PUT والعنوان الصحيح) بدل أن تُفتح كإضافة جديدة.
        $editingId = old('editing_id');
        $isEditReopen = $errors->any() && $editingId;
    @endphp
    <div class="sq-stack">
        <div class="sq-between">
            <div>
                <h2 style="margin:0;font-family:var(--font-heading);font-size:26px;">{{ __('app.nav_categories') }}</h2>
                <p class="sq-mute" style="margin:0;font-size:13px;">{{ __('app.categories_sub') }}</p>
            </div>
            <button type="button" class="btn btn-primary sq-tap" onclick="sqNewCategory()" style="min-height:42px;">
                <x-icon name="plus" size="18" /> {{ __('app.new_category') }}
            </button>
        </div>

        @foreach (['income', 'expense'] as $type)
            @php $list = $categories->where('type', $type); $color = $type === 'income' ? 'var(--sq-in)' : 'var(--sq-out)'; @endphp
            <section style="display:flex;flex-direction:column;gap:12px;">
                <div class="sq-row" style="gap:10px;">
                    <span class="sq-icon-box" style="width:22px;height:22px;color:{{ $color }};border-color:color-mix(in srgb,{{ $color }} 45%,transparent);">
                        <x-icon :name="$type === 'income' ? 'up' : 'down'" size="14" />
                    </span>
                    <h3 style="margin:0;font-family:var(--font-heading);font-size:19px;">
                        {{ $type === 'income' ? __('app.income_categories') : __('app.expense_categories') }}
                    </h3>
                    <span class="tag tag-neutral">{{ $list->count() }}</span>
                </div>

                <div class="sq-grid sq-g4" style="gap:12px;">
                    @foreach ($list as $cat)
                        <div class="card blueprint sq-lift" style="padding:16px;gap:10px;">
                            <x-corners />
                            <div class="sq-between" style="align-items:flex-start;">
                                <span class="sq-icon-box" style="width:36px;height:36px;color:{{ $color }};">
                                    <x-icon :name="$cat->icon" size="20" />
                                </span>
                                <span style="display:inline-flex;gap:6px;">
                                    {{-- التعديل يفتح النافذة نفسها مملوءة بقيم هذا التصنيف عبر data-* --}}
                                    <button type="button" class="btn btn-secondary btn-icon sq-tap"
                                            onclick="sqEditCategory(this)"
                                            data-category-id="{{ $cat->id }}"
                                            data-update-url="{{ route('categories.update', $cat) }}"
                                            data-name-ar="{{ $cat->name_ar }}"
                                            data-name-en="{{ $cat->name_en }}"
                                            data-type="{{ $cat->type }}"
                                            title="{{ __('app.edit') }}" aria-label="{{ __('app.edit') }}">
                                        <x-icon name="pencil" size="15" />
                                    </button>
                                    <form id="cat-del-{{ $cat->id }}" method="POST" action="{{ route('categories.destroy', $cat) }}" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-secondary btn-icon sq-tap"
                                                onclick="sqConfirmDelete('cat-del-{{ $cat->id }}', @js($cat->name))"
                                                title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}">
                                            <x-icon name="trash" size="15" />
                                        </button>
                                    </form>
                                </span>
                            </div>
                            <div style="font-family:var(--font-heading);font-size:17px;">{{ $cat->name }}</div>
                            <div class="sq-mute" style="font-size:12px;">
                                {{ $cat->transactions_count }} {{ __('app.tx_count') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    {{-- bottom sheet on mobile, centred dialog on desktop — one component, one markup.
         النافذة نفسها تخدم الإضافة والتعديل؛ JS يبدّل المسار والأسلوب والعنوان. --}}
    <div id="sq-cat-sheet" class="sq-sheet"
         data-new-label="{{ __('app.new_category') }}"
         data-edit-label="{{ __('app.edit_category') }}" hidden>
        <form id="sq-cat-form" method="POST"
              data-store-url="{{ route('categories.store') }}"
              action="{{ $isEditReopen ? route('categories.update', $editingId) : route('categories.store') }}"
              class="sq-sheet-panel blueprint">
            @csrf
            {{-- تزوير الأسلوب: فارغ = POST (إضافة)، PUT = تعديل. تضبطه JS عند الفتح. --}}
            <input type="hidden" name="_method" id="sq-cat-method" value="{{ $isEditReopen ? 'PUT' : '' }}">
            {{-- معرّف التصنيف قيد التعديل؛ يعيد ضبط النافذة على وضع التعديل بعد فشل التحقق --}}
            <input type="hidden" name="editing_id" value="{{ $isEditReopen ? $editingId : '' }}">
            {{-- يحمل المستخدم إلى حيث كان (نموذج الحركة) بعد الحفظ --}}
            @if ($returnTo)
                <input type="hidden" name="return" value="{{ $returnTo }}">
            @endif
            <x-corners />
            <div class="sq-sheet-head">
                <h2 id="sq-cat-title" style="margin:0;flex:1;font-family:var(--font-heading);font-size:19px;">
                    {{ $isEditReopen ? __('app.edit_category') : __('app.new_category') }}
                </h2>
                <button type="button" class="btn btn-secondary btn-icon sq-tap" data-sq-close aria-label="{{ __('app.close') }}">
                    <x-icon name="x" size="17" />
                </button>
            </div>
            <div class="sq-sheet-body">
                <div class="field">
                    <label for="cat-name-ar">{{ __('app.category_name_ar') }}</label>
                    <input id="cat-name-ar" class="input" type="text" name="name_ar" value="{{ old('name_ar') }}"
                           placeholder="{{ __('app.category_name_ar_ph') }}" dir="rtl" required
                           style="min-height:48px;font-size:15px;">
                    @error('name_ar') <span class="sq-mute" style="color:var(--sq-out);font-size:12px;">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="cat-name-en">{{ __('app.category_name_en') }}</label>
                    <input id="cat-name-en" class="input" type="text" name="name_en" value="{{ old('name_en') }}"
                           placeholder="{{ __('app.category_name_en_ph') }}" dir="ltr" required
                           style="min-height:48px;font-size:15px;">
                    @error('name_en') <span class="sq-mute" style="color:var(--sq-out);font-size:12px;">{{ $message }}</span> @enderror
                </div>
                @php
                    // النوع يأتي من نموذج الحركة عند القدوم منه، ويعود مع old()
                    // إن فشل التحقق. وإلا فالافتراضي "دخل" كما في التصميم.
                    $presetType = old('type', request('type') === 'expense' ? 'expense' : 'income');
                @endphp
                <div>
                    <div class="sq-mute" style="font-size:12px;margin-bottom:6px;">{{ __('app.type') }}</div>
                    <div class="sq-seg" style="width:100%;">
                        <label class="sq-seg-opt" style="flex:1;">
                            <input type="radio" name="type" value="income" @checked($presetType === 'income') style="margin-inline-end:6px;">
                            {{ __('app.income') }}
                        </label>
                        <label class="sq-seg-opt" style="flex:1;">
                            <input type="radio" name="type" value="expense" @checked($presetType === 'expense') style="margin-inline-end:6px;">
                            {{ __('app.expense') }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="sq-sheet-foot">
                <button type="button" class="btn btn-secondary sq-tap sq-only-desk" data-sq-close style="min-height:48px;flex:1;">
                    {{ __('app.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary sq-tap" style="min-height:48px;flex:2;">
                    <x-icon name="check" size="18" /> {{ __('app.save') }}
                </button>
            </div>
        </form>
    </div>

    @if ($returnTo || $errors->any())
        {{-- نفتح النافذة فورًا في حالتين: قدوم المستخدم من نموذج حركة ليضيف تصنيفًا،
             أو فشل التحقق فنعيد فتحها ليرى رسائل الخطأ ويصحّح بدل أن تختفي منه. --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () { sqOpen('sq-cat-sheet'); });
        </script>
    @endif
</x-layouts.app>
