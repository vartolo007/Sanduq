<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * قواعد تعديل بيانات الحساب.
 */
class UpdateProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // ignore يستثني سجل المستخدم نفسه من فحص التفرّد، وإلا لرفض
                // الحفظ لمجرد أنه أبقى بريده كما هو.
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
        ];
    }
}
