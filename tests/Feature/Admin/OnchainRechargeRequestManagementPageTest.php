<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Balance\Models\RechargePaymentRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnchainRechargeRequestManagementPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_onchain_recharge_request_pages_in_local_environment(): void
    {
        $user = User::factory()->create();

        RechargePaymentRequest::query()->create([
            'user_id' => $user->id,
            'contact_account' => (string) $user->username,
            'asset_code' => 'USDT',
            'payment_amount' => 88.66,
            'currency' => 'USDT',
            'network' => 'BSC',
            'receipt_address' => '0x1111111111111111111111111111111111111111',
            'receipt_image_path' => 'onchain/placeholder',
            'channel' => 'onchain_wallet',
            'tx_hash' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'chain_id' => '56',
            'from_address' => '0x2222222222222222222222222222222222222222',
            'to_address' => '0x1111111111111111111111111111111111111111',
            'tx_submitted_at' => now(),
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('链上充值申请');

        $this->get('/admin/onchain-recharge-requests')
            ->assertOk()
            ->assertSee('交易哈希')
            ->assertSee('链ID')
            ->assertSee('确认入账');
    }
}
