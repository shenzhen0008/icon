<?php

namespace Tests\Feature\OnchainRecharge;

use App\Models\User;
use App\Modules\Balance\Models\RechargePaymentRequest;
use App\Modules\Balance\Models\RechargeReceiver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitOnchainRechargeRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_submit_onchain_recharge_request(): void
    {
        $this->createReceiver('USDT', 'BSC', '0x1111111111111111111111111111111111111111');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/recharge/onchain/requests', [
            'asset_code' => 'USDT',
            'payment_amount' => '100.50',
            'tx_hash' => '0xAaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'chain_id' => '56',
            'from_address' => '0x2222222222222222222222222222222222222222',
            'user_note' => 'onchain test',
        ]);

        $response
            ->assertRedirect('/recharge/onchain')
            ->assertSessionHas('success', '链上充值申请已提交，请等待客服核账。');

        $this->assertDatabaseHas('recharge_payment_requests', [
            'user_id' => $user->id,
            'asset_code' => 'USDT',
            'payment_amount' => '100.50',
            'channel' => 'onchain_wallet',
            'tx_hash' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'chain_id' => '56',
            'from_address' => '0x2222222222222222222222222222222222222222',
            'status' => 'pending',
        ]);
    }

    public function test_guest_cannot_submit_onchain_recharge_request(): void
    {
        $this->createReceiver('USDT', 'BSC', '0x1111111111111111111111111111111111111111');

        $this->post('/recharge/onchain/requests', [
            'asset_code' => 'USDT',
            'payment_amount' => '10',
            'tx_hash' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'chain_id' => '56',
        ])->assertRedirect('/login');
    }

    public function test_submission_rejects_invalid_input_and_duplicate_hash(): void
    {
        $this->createReceiver('USDT', 'BSC', '0x1111111111111111111111111111111111111111');

        $user = User::factory()->create();

        RechargePaymentRequest::query()->create([
            'user_id' => $user->id,
            'contact_account' => (string) $user->username,
            'asset_code' => 'USDT',
            'payment_amount' => '88.00',
            'currency' => 'USDT',
            'network' => 'BSC',
            'receipt_address' => '0x1111111111111111111111111111111111111111',
            'receipt_image_path' => 'onchain/placeholder',
            'channel' => 'onchain_wallet',
            'tx_hash' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'chain_id' => '56',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->from('/recharge/onchain')
            ->post('/recharge/onchain/requests', [
                'asset_code' => 'USDT',
                'payment_amount' => '0',
                'tx_hash' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'chain_id' => '',
            ])
            ->assertRedirect('/recharge/onchain')
            ->assertSessionHasErrors(['payment_amount', 'tx_hash', 'chain_id']);
    }

    private function createReceiver(string $assetCode, string $network, string $address, bool $isActive = true): RechargeReceiver
    {
        return RechargeReceiver::query()->updateOrCreate([
            'asset_code' => $assetCode,
        ], [
            'asset_name' => $assetCode,
            'network' => $network,
            'address' => $address,
            'is_active' => $isActive,
            'sort' => 0,
        ]);
    }
}
