<?php

namespace App\Modules\BankRecharge\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankRechargePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_key' => ['required', 'string', 'max:50'],
            'payment_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'receipt_image' => ['required', 'image', 'max:5120'],
            'user_note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
