<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Withdrawal\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalRequestManagementPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_withdrawal_request_pages_in_local_environment(): void
    {
        $user = User::factory()->create();

        WithdrawalRequest::query()->create([
            'user_id' => $user->id,
            'asset_code' => 'USDT',
            'network' => 'TRC20',
            'destination_address' => 'T1234567890abcdef',
            'amount' => '100.00',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
        WithdrawalRequest::query()->create([
            'user_id' => $user->id,
            'asset_code' => 'BANK',
            'network' => 'BANK_CARD',
            'destination_address' => '6222021234567890123',
            'meta_json' => [
                'bank_name' => 'China Bank',
                'account_name' => 'Zhang San',
                'card_number' => '6222021234567890123',
            ],
            'amount' => '200.00',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('提款申请');

        $this->get('/admin/withdrawal-requests')
            ->assertOk()
            ->assertSee('收款地址/卡号')
            ->assertSee('银行卡快照')
            ->assertSee('China Bank / Zhang San / 6222021234567890123')
            ->assertSee('提款金额')
            ->assertSee('状态');
    }
}
