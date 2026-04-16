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
}
