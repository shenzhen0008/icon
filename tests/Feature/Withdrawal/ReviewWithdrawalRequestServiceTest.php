<?php

namespace Tests\Feature\Withdrawal;

use App\Models\User;
use App\Modules\Withdrawal\Models\WithdrawalRequest;
use App\Modules\Withdrawal\Services\ReviewWithdrawalRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReviewWithdrawalRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_processed_updates_request_without_second_debit(): void
    {
        $user = User::factory()->create([
            'balance' => '400.00',
        ]);
        $admin = User::factory()->create();

        $request = WithdrawalRequest::query()->create([
            'user_id' => $user->id,
            'asset_code' => 'USDT',
            'network' => 'TRC20',
            'destination_address' => 'T1234567890abcdef',
            'amount' => '100.00',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        app(ReviewWithdrawalRequestService::class)->markProcessed($request->id, $admin->id, '已打款');

        $this->assertDatabaseHas('withdrawal_requests', [
            'id' => $request->id,
            'status' => 'processed',
            'reviewed_by' => $admin->id,
            'review_note' => '已打款',
        ]);

        $user->refresh();
        $this->assertSame('400.00', number_format((float) $user->balance, 2, '.', ''));
    }

    public function test_reject_refunds_balance_and_creates_refund_ledger(): void
    {
        $user = User::factory()->create([
            'balance' => '400.00',
        ]);
        $admin = User::factory()->create();

        $request = WithdrawalRequest::query()->create([
            'user_id' => $user->id,
            'asset_code' => 'USDT',
            'network' => 'TRC20',
            'destination_address' => 'T1234567890abcdef',
            'amount' => '100.00',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        app(ReviewWithdrawalRequestService::class)->reject($request->id, $admin->id, '地址无效');

        $this->assertDatabaseHas('withdrawal_requests', [
            'id' => $request->id,
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'review_note' => '地址无效',
        ]);

        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'withdrawal_refund',
            'amount' => '100.00',
            'before_balance' => '400.00',
            'after_balance' => '500.00',
            'biz_ref_type' => 'withdrawal_request',
            'biz_ref_id' => (string) $request->id,
        ]);

        $user->refresh();
        $this->assertSame('500.00', number_format((float) $user->balance, 2, '.', ''));
    }

    public function test_mark_processed_rejects_repeat_review(): void
    {
        $admin = User::factory()->create();
        $request = WithdrawalRequest::query()->create([
            'user_id' => User::factory()->create()->id,
            'asset_code' => 'USDT',
            'network' => 'TRC20',
            'destination_address' => 'T1234567890abcdef',
            'amount' => '100.00',
            'status' => 'processed',
            'submitted_at' => now(),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        app(ReviewWithdrawalRequestService::class)->markProcessed($request->id, $admin->id, '重复处理');
    }
}
