<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * قواعد إضافة وتعديل الحركات المالية — المتطلبان 3.2.1 و 3.2.3.
 *
 * نستخدم نفس الملف للإضافة والتعديل لأن الحقول والقواعد متطابقة.
 */
class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ملكية السجل يتحقق منها TransactionPolicy داخل الكنترولر،
        // لأن الطلب هنا قد يكون إنشاءً لا سجل له بعد.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['income', 'expense'])],

            // قاعدة واحدة تضمن شرطين معًا:
            // 1) التصنيف يخص المستخدم الحالي — فلا يربط حركته بتصنيف غيره
            // 2) نوع التصنيف يطابق نوع الحركة — شرط صريح في المتطلب 3.2.1
            //    ("فئة دخل لا تظهر عند اختيار نوع مصروف والعكس")
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query
                        ->where('user_id', $this->user()->id)
                        ->where('type', $this->input('type'))
                ),
            ],

            // gt:0 هو شرط "المبلغ رقم موجب أكبر من صفر" في المتطلب 3.2.1
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],

            'date' => ['required', 'date'],

            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => __('app.type'),
            'category_id' => __('app.category'),
            'amount' => __('app.amount'),
            'date' => __('app.date'),
            'note' => __('app.note'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // الرسالة الافتراضية لـ exists غامضة هنا، لأن الفشل غالبًا سببه
            // اختيار تصنيف لا يطابق نوع الحركة وليس تصنيفًا غير موجود.
            'category_id.exists' => __('app.err_category_type'),
        ];
    }
}
