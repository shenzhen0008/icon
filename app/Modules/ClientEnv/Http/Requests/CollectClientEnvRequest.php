<?php

namespace App\Modules\ClientEnv\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CollectClientEnvRequest extends FormRequest
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
            'client' => ['nullable', 'array'],
            'client.browser_name' => ['nullable', 'string', 'max:120'],
            'client.browser_version' => ['nullable', 'string', 'max:60'],
            'client.platform' => ['nullable', 'string', 'max:120'],
            'client.user_agent' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
