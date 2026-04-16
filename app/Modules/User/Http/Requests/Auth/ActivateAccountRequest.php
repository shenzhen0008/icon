<?php

namespace App\Modules\User\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ActivateAccountRequest extends FormRequest
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
            'password' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/', 'confirmed'],
            'invite_code' => ['nullable', 'string', 'max:32', 'regex:/^[A-Za-z0-9]*$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'password' => '交易 PIN',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.size' => '交易 PIN 必须为 6 位数字。',
            'password.regex' => '交易 PIN 必须为 6 位数字。',
            'password.confirmed' => '两次输入的交易 PIN 不一致。',
        ];
    }
}
