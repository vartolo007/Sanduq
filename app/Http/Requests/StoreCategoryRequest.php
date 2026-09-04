<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * قواعد إضافة تصنيف جديد — المتطلب 3.2.5.
 */
class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // اسمان: واحد بالعربية وواحد بالإنجليزية، ليتبع التصنيف لغة الواجهة عند التبديل.
            'name_ar' => [
                'required',
                'string',
                'max:255',
                // لا يكرّر المستخدم نفس الاسم داخل نفس النوع.
                // الشرط مقيّد بـ user_id حتى لا يمنع اسمًا لمجرد أن مستخدمًا آخر استخدمه.
                Rule::unique('categories', 'name_ar')->where(
                    fn ($query) => $query
                        ->where('user_id', $this->user()->id)
                        ->where('type', $this->input('type'))
                ),
            ],

            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name_en')->where(
                    fn ($query) => $query
                        ->where('user_id', $this->user()->id)
                        ->where('type', $this->input('type'))
                ),
            ],

            'type' => ['required', Rule::in(['income', 'expense'])],
        ];
    }

    /**
     * أسماء الحقول كما تظهر في رسائل الخطأ.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name_ar' => __('app.category_name_ar'),
            'name_en' => __('app.category_name_en'),
            'type' => __('app.type'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name_ar.unique' => __('app.err_category_exists'),
            'name_en.unique' => __('app.err_category_exists'),
        ];
    }
}
