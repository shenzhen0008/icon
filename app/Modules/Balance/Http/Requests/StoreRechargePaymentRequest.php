<?php

namespace App\Modules\Balance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRechargePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_code' => [
                'required',
                'string',
                Rule::exists('recharge_receivers', 'asset_code')
                    ->where(fn ($query) => $query->where('is_active', true)),
            ],
            'payment_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'receipt_image' => ['required', 'image', 'max:5120'],
            'user_note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
