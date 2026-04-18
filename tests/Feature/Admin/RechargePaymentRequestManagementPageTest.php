<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Balance\Models\RechargePaymentRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RechargePaymentRequestManagementPageTest extends AdminPanelTestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_recharge_payment_request_pages_in_local_environment(): void
    {
        $user = User::factory()->create();

        RechargePaymentRequest::query()->create([
            'user_id' => $user->id,
            'contact_account' => 'tg_u_001',
            'payment_amount' => 88.66,
            'currency' => 'USDT',
            'network' => 'TRC20',
            'receipt_image_path' => 'recharge-receipts/demo.png',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('充值申请');

        $this->get('/admin/recharge-payment-requests')
            ->assertOk()
            ->assertSee('联系账号')
            ->assertSee('付款金额')
            ->assertSee('付款截图');
    }
}
