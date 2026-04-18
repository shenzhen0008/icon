<?php

namespace Tests\Feature\Admin;

use App\Modules\Balance\Models\RechargeReceiver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RechargeReceiverManagementPageTest extends AdminPanelTestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_recharge_receiver_management_pages_in_local_environment(): void
    {
        $receiver = RechargeReceiver::query()->updateOrCreate([
            'asset_code' => 'USDT',
        ], [
            'asset_name' => 'USDT',
            'network' => 'TRC20',
            'address' => 'T-usdt',
            'is_active' => true,
            'sort' => 1,
        ]);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('收款账户');

        $this->get('/admin/recharge-receivers')
            ->assertOk()
            ->assertSee('币种代码')
            ->assertSee('收款地址')
            ->assertSee('启用');

        $this->get('/admin/recharge-receivers/create')
            ->assertOk()
            ->assertSee('币种代码')
            ->assertSee('收款地址')
            ->assertSee('USDT')
            ->assertSee('USDC')
            ->assertSee('BTC')
            ->assertSee('ETH')
            ->assertDontSee('DOGE');

        $this->get('/admin/recharge-receivers/'.$receiver->id.'/edit')
            ->assertOk()
            ->assertSee('币种代码')
            ->assertSee('收款地址');
    }
}
