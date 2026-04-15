<?php

namespace App\Modules\OnchainRecharge\Services;

use App\Models\User;
use App\Modules\Balance\Models\RechargePaymentRequest;
use App\Modules\Balance\Models\RechargeReceiver;
use App\Modules\OnchainRecharge\Support\OnchainRechargeStatus;
use App\Modules\OnchainRecharge\Support\TxHashNormalizer;
use Illuminate\Validation\ValidationException;

class CreateOnchainRechargeRequestService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(User $user, array $payload): RechargePaymentRequest
    {
        $assetCode = (string) ($payload['asset_code'] ?? '');

        $receiver = RechargeReceiver::query()
            ->where('asset_code', $assetCode)
            ->where('is_active', true)
            ->first();

        if ($receiver === null) {
            throw ValidationException::withMessages([
                'asset_code' => '所选币种已停用，请重新选择。',
            ]);
        }

        $txHash = TxHashNormalizer::normalize((string) ($payload['tx_hash'] ?? ''));

        $exists = RechargePaymentRequest::query()
            ->where('channel', OnchainRechargeStatus::CHANNEL_ONCHAIN_WALLET)
            ->where('tx_hash', $txHash)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'tx_hash' => '该交易哈希已提交，请勿重复提交。',
            ]);
        }

        $toAddress = $this->resolveToAddress($assetCode, (string) $receiver->network, (string) $receiver->address);

        return RechargePaymentRequest::query()->create([
            'user_id' => $user->id,
            'contact_account' => (string) $user->username,
            'asset_code' => $assetCode,
            'payment_amount' => (string) ($payload['payment_amount'] ?? '0'),
            'currency' => $assetCode,
            'network' => (string) $receiver->network,
            'receipt_address' => $toAddress,
            'receipt_image_path' => 'onchain/placeholder',
            'channel' => OnchainRechargeStatus::CHANNEL_ONCHAIN_WALLET,
            'tx_hash' => $txHash,
            'chain_id' => (string) ($payload['chain_id'] ?? ''),
            'from_address' => (string) ($payload['from_address'] ?? ''),
            'to_address' => $toAddress,
            'tx_submitted_at' => now(),
            'status' => OnchainRechargeStatus::STATUS_PENDING,
            'user_note' => $payload['user_note'] ?? null,
            'submitted_at' => now(),
        ]);
    }

    private function resolveToAddress(string $assetCode, string $network, string $fallbackAddress): string
    {
        $networkAddresses = (array) config('web3.treasury_addresses', []);
        $assetAddresses = (array) ($networkAddresses[$assetCode] ?? []);
        $configuredAddress = $assetAddresses[$network] ?? null;

        if (! is_string($configuredAddress) || $configuredAddress === '') {
            return $fallbackAddress;
        }

        return $configuredAddress;
    }
}
