<?php

namespace Tests\Feature\OnchainRecharge;

use App\Models\User;
use App\Modules\Balance\Models\RechargePaymentRequest;
use App\Modules\OnchainRecharge\Services\ReviewOnchainRechargeRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReviewOnchainRechargeRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_processed_credits_balance_for_onchain_request(): void
    {
        $user = User::factory()->create(['balance' => 100]);
        $admin = User::factory()->create();

        $request = RechargePaymentRequest::query()->create([
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

        app(ReviewOnchainRechargeRequestService::class)->markProcessed($request->id, $admin->id, '链上已核账');

        $this->assertDatabaseHas('recharge_payment_requests', [
            'id' => $request->id,
            'status' => 'processed',
            'reviewed_by' => $admin->id,
            'review_note' => '链上已核账',
        ]);

        $user->refresh();
        $this->assertSame('188.66', number_format((float) $user->balance, 2, '.', ''));

        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'recharge_credit',
            'biz_ref_type' => 'recharge_payment_request',
            'biz_ref_id' => (string) $request->id,
        ]);
    }

    public function test_mark_processed_rejects_repeat_review(): void
    {
        $user = User::factory()->create(['balance' => 100]);
        $admin = User::factory()->create();

        $request = RechargePaymentRequest::query()->create([
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
            'status' => 'processed',
            'submitted_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        app(ReviewOnchainRechargeRequestService::class)->markProcessed($request->id, $admin->id, '重复处理');
    }

    public function test_reject_marks_request_without_crediting_balance(): void
    {
        $user = User::factory()->create(['balance' => 100]);
        $admin = User::factory()->create();

        $request = RechargePaymentRequest::query()->create([
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

        app(ReviewOnchainRechargeRequestService::class)->reject($request->id, $admin->id, '金额不符');

        $this->assertDatabaseHas('recharge_payment_requests', [
            'id' => $request->id,
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'review_note' => '金额不符',
        ]);

        $user->refresh();
        $this->assertSame('100.00', number_format((float) $user->balance, 2, '.', ''));
    }
}
