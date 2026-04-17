<?php

namespace App\Modules\Withdrawal\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_code' => ['required', 'string', Rule::in(['USDT', 'BANK'])],
            'network' => ['required', 'string', 'max:20'],
            'destination_address' => ['nullable', 'required_unless:network,BANK_CARD', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'bank_name' => ['nullable', 'required_if:network,BANK_CARD', 'string', 'max:100'],
            'account_name' => ['nullable', 'required_if:network,BANK_CARD', 'string', 'max:100'],
            'card_number' => ['nullable', 'required_if:network,BANK_CARD', 'string', 'digits_between:12,30'],
            'branch_name' => ['nullable', 'string', 'max:120'],
            'reserved_phone' => ['nullable', 'string', 'max:20'],
            'bank_note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
