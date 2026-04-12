<?php

namespace Tests\Feature\Balance;

use App\Models\User;
use App\Modules\Balance\Models\RechargePaymentRequest;
use App\Modules\Balance\Services\ReviewRechargePaymentRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RechargePaymentRequestReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_processed_credits_user_balance_and_creates_ledger(): void
    {
        $user = User::factory()->create([
            'balance' => 100,
        ]);
        $admin = User::factory()->create();

        $request = RechargePaymentRequest::query()->create([
            'user_id' => $user->id,
            'contact_account' => 'tg_u_001',
            'asset_code' => 'USDT',
            'payment_amount' => 88.66,
            'currency' => 'USDT',
            'network' => 'TRC20',
            'receipt_image_path' => 'recharge-receipts/demo.png',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        app(ReviewRechargePaymentRequestService::class)->markProcessed($request->id, $admin->id, '已确认到账');

        $this->assertDatabaseHas('recharge_payment_requests', [
            'id' => $request->id,
            'status' => 'processed',
            'reviewed_by' => $admin->id,
            'review_note' => '已确认到账',
        ]);

        $user->refresh();
        $this->assertSame('188.66', number_format((float) $user->balance, 2, '.', ''));

        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'recharge_credit',
            'amount' => 88.66,
            'before_balance' => 100,
            'after_balance' => 188.66,
            'biz_ref_type' => 'recharge_payment_request',
            'biz_ref_id' => (string) $request->id,
        ]);
    }

    public function test_mark_processed_rejects_repeat_review(): void
    {
        $user = User::factory()->create([
            'balance' => 100,
        ]);
        $admin = User::factory()->create();

        $request = RechargePaymentRequest::query()->create([
            'user_id' => $user->id,
            'contact_account' => 'tg_u_001',
            'asset_code' => 'USDT',
            'payment_amount' => 88.66,
            'currency' => 'USDT',
            'network' => 'TRC20',
            'receipt_image_path' => 'recharge-receipts/demo.png',
            'status' => 'processed',
            'submitted_at' => now(),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        app(ReviewRechargePaymentRequestService::class)->markProcessed($request->id, $admin->id, '重复处理');
    }

    public function test_reject_marks_request_without_crediting_balance(): void
    {
        $user = User::factory()->create([
            'balance' => 100,
        ]);
        $admin = User::factory()->create();

        $request = RechargePaymentRequest::query()->create([
            'user_id' => $user->id,
            'contact_account' => 'tg_u_001',
            'asset_code' => 'USDT',
            'payment_amount' => 88.66,
            'currency' => 'USDT',
            'network' => 'TRC20',
            'receipt_image_path' => 'recharge-receipts/demo.png',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        app(ReviewRechargePaymentRequestService::class)->reject($request->id, $admin->id, '金额不符');

        $this->assertDatabaseHas('recharge_payment_requests', [
            'id' => $request->id,
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'review_note' => '金额不符',
        ]);

        $user->refresh();
        $this->assertSame('100.00', number_format((float) $user->balance, 2, '.', ''));

        $this->assertDatabaseMissing('balance_ledgers', [
            'user_id' => $user->id,
            'biz_ref_type' => 'recharge_payment_request',
            'biz_ref_id' => (string) $request->id,
        ]);
    }
}
