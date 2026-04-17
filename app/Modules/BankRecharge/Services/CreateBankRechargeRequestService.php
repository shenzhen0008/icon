<?php

namespace App\Modules\BankRecharge\Services;

use App\Models\User;
use App\Modules\Balance\Models\RechargePaymentRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class CreateBankRechargeRequestService
{
    public function handle(User $user, string $receiverKey, string $paymentAmount, UploadedFile $receiptImage, null|string $userNote = null): RechargePaymentRequest
    {
        $receivers = $this->resolveBankReceivers();
        $receiver = $receivers[$receiverKey] ?? null;
        if (! is_array($receiver)) {
            throw ValidationException::withMessages([
                'receiver_key' => '所选收款银行不可用，请重新选择。',
            ]);
        }

        $receiverCardNumber = (string) ($receiver['card_number'] ?? '');

        $receiptImagePath = $receiptImage->store('recharge-receipts', 'public');
        if (! is_string($receiptImagePath) || $receiptImagePath === '') {
            throw ValidationException::withMessages([
                'receipt_image' => '付款截图上传失败，请重试。',
            ]);
        }

        return RechargePaymentRequest::query()->create([
            'user_id' => $user->id,
            'contact_account' => (string) $user->username,
            'asset_code' => 'BANK',
            'payment_amount' => $paymentAmount,
            'currency' => 'CNY',
            'network' => 'BANK_TRANSFER',
            'receipt_address' => $receiverCardNumber,
            'receipt_image_path' => $receiptImagePath,
            'channel' => 'bank_card_manual',
            'status' => 'pending',
            'user_note' => $userNote,
            'submitted_at' => now(),
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function resolveBankReceivers(): array
    {
        $configured = (array) config('recharge.bank_receivers', []);
        $receivers = [];

        foreach ($configured as $key => $receiver) {
            if (! is_array($receiver)) {
                continue;
            }

            $enabled = (bool) ($receiver['enabled'] ?? false);
            $cardNumber = (string) ($receiver['card_number'] ?? '');
            $accountName = (string) ($receiver['account_name'] ?? '');
            if (! $enabled || $cardNumber === '' || $accountName === '') {
                continue;
            }

            $receivers[(string) $key] = [
                'card_number' => $cardNumber,
            ];
        }

        if ($receivers === []) {
            $legacy = (array) config('recharge.bank_receiver', []);
            $enabled = (bool) ($legacy['enabled'] ?? false);
            $cardNumber = (string) ($legacy['card_number'] ?? '');
            $accountName = (string) ($legacy['account_name'] ?? '');
            if ($enabled && $cardNumber !== '' && $accountName !== '') {
                $receivers['legacy'] = [
                    'card_number' => $cardNumber,
                ];
            }
        }

        return $receivers;
    }
}
