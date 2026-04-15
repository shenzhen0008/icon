<?php

namespace App\Modules\OnchainRecharge\Http\Requests;

use App\Modules\OnchainRecharge\Support\OnchainRechargeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOnchainRechargeRequest extends FormRequest
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
            'tx_hash' => [
                'required',
                'string',
                'regex:/^0x[a-fA-F0-9]{64}$/',
                Rule::unique('recharge_payment_requests', 'tx_hash')
                    ->where(fn ($query) => $query->where('channel', OnchainRechargeStatus::CHANNEL_ONCHAIN_WALLET)),
            ],
            'chain_id' => ['required', 'string', 'max:32'],
            'from_address' => ['nullable', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'user_note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
