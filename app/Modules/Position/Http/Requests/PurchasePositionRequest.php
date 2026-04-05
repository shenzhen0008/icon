<?php

namespace App\Modules\Position\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchasePositionRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'shares' => ['required', 'integer', 'min:1'],
        ];
    }
}
