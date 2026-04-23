<?php

namespace App\Modules\User\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class MnemonicLoginRequest extends FormRequest
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
            'mnemonic_phrase' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function remember(): bool
    {
        return $this->boolean('remember');
    }
}
