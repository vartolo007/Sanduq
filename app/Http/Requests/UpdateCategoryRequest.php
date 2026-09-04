<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * قواعد تعديل تصنيف قائم — المتطلب 3.2.5.
 *
 * تطابق قواعد الإضافة، مع فارق واحد: فحص التكرار يتجاهل التصنيف نفسه
 * حتى لا يمنع المستخدم من حفظ التصنيف باسمه الحالي دون تغييره.
 */
class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // الصلاحية الفعلية تُفحص عبر CategoryPolicy في الكنترولر؛ هنا نسمح
        // بالمرور إلى مرحلة التحقق فقط.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')->id;

        return [
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name_ar')
                    ->ignore($categoryId)
                    ->where(
                        fn ($query) => $query
                            ->where('user_id', $this->user()->id)
                            ->where('type', $this->input('type'))
                    ),
            ],

            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name_en')
                    ->ignore($categoryId)
                    ->where(
                        fn ($query) => $query
                            ->where('user_id', $this->user()->id)
                            ->where('type', $this->input('type'))
                    ),
            ],

            'type' => ['required', Rule::in(['income', 'expense'])],
        ];
    }

    /**
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
