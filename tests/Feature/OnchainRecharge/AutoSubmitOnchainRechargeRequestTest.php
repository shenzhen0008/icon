<?php

namespace Tests\Feature\OnchainRecharge;

use App\Models\User;
use App\Modules\Balance\Models\RechargeReceiver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoSubmitOnchainRechargeRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_auto_submit_onchain_recharge_request(): void
    {
        $this->createReceiver('USDT', 'BSC', '0x1111111111111111111111111111111111111111');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/recharge/onchain/requests/auto', [
            'asset_code' => 'USDT',
            'payment_amount' => '10.00',
            'tx_hash' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'chain_id' => '56',
            'from_address' => '0x2222222222222222222222222222222222222222',
        ]);

        $response->assertOk()->assertJson([
            'message' => '链上充值申请已自动提交，请等待客服核账。',
        ]);

        $this->assertDatabaseHas('recharge_payment_requests', [
            'user_id' => $user->id,
            'asset_code' => 'USDT',
            'payment_amount' => '10.00',
            'channel' => 'onchain_wallet',
            'tx_hash' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'chain_id' => '56',
            'from_address' => '0x2222222222222222222222222222222222222222',
            'status' => 'pending',
        ]);
    }

    public function test_guest_cannot_auto_submit_onchain_recharge_request(): void
    {
        $this->createReceiver('USDT', 'BSC', '0x1111111111111111111111111111111111111111');

        $this->postJson('/recharge/onchain/requests/auto', [
            'asset_code' => 'USDT',
            'payment_amount' => '10.00',
            'tx_hash' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'chain_id' => '56',
        ])->assertUnauthorized();
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

