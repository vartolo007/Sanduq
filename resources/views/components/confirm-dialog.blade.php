{{-- One dialog serves every destructive action on the page; sqConfirmDelete() wires the form. --}}
<div id="sq-confirm" class="dialog-backdrop" hidden style="z-index:70;">
    <div class="dialog blueprint" role="alertdialog" aria-modal="true" aria-labelledby="sq-confirm-title"
         style="background:var(--color-bg);">
        <x-corners />
        <div style="display:flex;gap:12px;align-items:flex-start;">
            <span class="sq-icon-box" style="width:36px;height:36px;border-color:var(--sq-out);color:var(--sq-out);">
                <x-icon name="trash" size="18" />
            </span>
            <div style="min-width:0;">
                <div class="dialog-title" id="sq-confirm-title">{{ __('app.confirm_del_title') }}</div>
                <div class="dialog-body">
                    {{ __('app.confirm_del_body') }}
                    <span data-confirm-label style="display:block;margin-top:4px;font-family:var(--font-heading);"></span>
                </div>
            </div>
        </div>
        <div class="dialog-actions">
            <button type="button" class="btn btn-secondary sq-tap" data-sq-close style="min-height:44px;">{{ __('app.cancel') }}</button>
            <button type="button" class="btn btn-primary sq-tap" data-confirm-target
                    style="min-height:44px;background:var(--sq-out);border-color:var(--sq-out);">
                {{ __('app.confirm_del_cta') }}
            </button>
        </div>
    </div>
</div>
