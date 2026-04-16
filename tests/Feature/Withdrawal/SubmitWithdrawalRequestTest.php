<?php

namespace Tests\Feature\Withdrawal;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitWithdrawalRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_submit_withdrawal_request(): void
    {
        $this->post('/withdrawal-requests', [
            'asset_code' => 'USDT',
            'network' => 'TRC20',
            'destination_address' => 'T1234567890abcdef',
            'amount' => '100.00',
        ])->assertRedirect('/login');
    }

    public function test_authenticated_user_cannot_submit_invalid_withdrawal_request(): void
    {
        $user = User::factory()->create([
            'balance' => '500.00',
        ]);

        $this->actingAs($user)
            ->from('/recharge?mode=send')
            ->post('/withdrawal-requests', [
                'asset_code' => 'USDT',
                'network' => 'TRC20',
                'destination_address' => '',
                'amount' => '0',
            ])
            ->assertRedirect('/recharge?mode=send')
            ->assertSessionHasErrors(['destination_address', 'amount']);
    }

    public function test_authenticated_user_cannot_submit_withdrawal_request_exceeding_balance(): void
    {
        $user = User::factory()->create([
            'balance' => '500.00',
        ]);

        $this->actingAs($user)
            ->from('/recharge?mode=send')
            ->post('/withdrawal-requests', [
                'asset_code' => 'USDT',
                'network' => 'TRC20',
                'destination_address' => 'T1234567890abcdef',
                'amount' => '600.00',
            ])
            ->assertRedirect('/recharge?mode=send')
            ->assertSessionHasErrors(['amount']);
    }

    public function test_authenticated_user_submission_deducts_balance_and_creates_pending_withdrawal_request(): void
    {
        $user = User::factory()->create([
            'balance' => '500.00',
        ]);

        $this->actingAs($user)
            ->post('/withdrawal-requests', [
                'asset_code' => 'USDT',
                'network' => 'TRC20',
                'destination_address' => 'T1234567890abcdef',
                'amount' => '100.00',
            ])
            ->assertRedirect('/recharge?mode=send')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $user->id,
            'asset_code' => 'USDT',
            'network' => 'TRC20',
            'destination_address' => 'T1234567890abcdef',
            'amount' => '100.00',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'withdrawal_debit',
            'amount' => '-100.00',
            'before_balance' => '500.00',
            'after_balance' => '400.00',
            'biz_ref_type' => 'withdrawal_request',
        ]);

        $this->assertSame('400.00', User::query()->findOrFail($user->id)->balance);
    }

    public function test_send_mode_renders_real_withdrawal_form_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'balance' => '500.00',
        ]);

        $this->actingAs($user)
            ->get('/recharge?mode=send')
            ->assertOk()
            ->assertSee('提款（SEND）')
            ->assertSee('action="/withdrawal-requests"', false)
            ->assertSee('name="destination_address"', false)
            ->assertSee('name="amount"', false)
            ->assertSee('提交提款申请')
            ->assertSee('500.00')
            ->assertDontSee('请在下一阶段接入真实提款地址输入与校验')
            ->assertDontSee('请在下一阶段接入真实提款金额输入与校验')
            ->assertDontSee('当前仅完成页面交互与内容占位，提款提交流程待后端接口上线后接通。');
    }
}
