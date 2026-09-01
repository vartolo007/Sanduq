<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * قواعد التحقق الخاصة بإنشاء حساب جديد — المتطلب 3.1.1 في وثيقة SRS.
 *
 * فائدة FormRequest: يتحقق Laravel من القواعد *قبل* أن يصل الطلب إلى
 * الـ Controller. إذا فشل التحقق، يُعاد المستخدم تلقائيًا إلى الصفحة السابقة
 * مع رسائل الخطأ ومع المدخلات القديمة، فيبقى الـ Controller نظيفًا.
 */
class RegisterRequest extends FormRequest
{
    /**
     * أي زائر يستطيع فتح صفحة التسجيل، لذلك لا يوجد شرط صلاحية هنا.
     */
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
            'name' => ['required', 'string', 'max:255'],

            // unique:users يضمن شرط "البريد يجب أن يكون فريدًا" من المتطلب 3.1.1
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],

            // confirmed يبحث تلقائيًا عن حقل اسمه password_confirmation ويطابقه
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}
