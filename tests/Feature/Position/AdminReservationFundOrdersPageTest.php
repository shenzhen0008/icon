<?php

namespace Tests\Feature\Position;

use App\Models\User;
use App\Modules\Balance\Models\AdminReservationFundOrder as Order;
use App\Modules\Balance\Services\CreateAdminReservationFundOrderService;
use App\Modules\Balance\Services\ReleaseAdminReservationFundOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReservationFundOrdersPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_page_shows_only_the_users_orders_in_reverse_order(): void
    {
        $this->freezeSecond();
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['balance' => '200.00']);
        $other = User::factory()->create();
        $service = app(CreateAdminReservationFundOrderService::class);
        $name = '予約 🌟 العربية <b>literal</b>';

        $order = $service->handle($admin, $user->id, ['type' => Order::DEBIT, 'name' => $name, 'amount' => '100.50']);
        $newerOrder = $service->handle($admin, $user->id, ['name' => '较新的扣款', 'amount' => '2.00']);
        $service->handle($admin, $other->id, ['name' => '另一用户的订单', 'amount' => '9.00']);
        Order::query()->create(['user_id' => $user->id, 'type' => Order::RELEASE, 'name' => '历史独立解冻', 'amount' => '2.00']);

        $this->actingAs($user)->get('/me/orders')
            ->assertOk()
            ->assertSee('链上预约资金')
            ->assertSeeInOrder(['较新的扣款', '-2.00 USDT', $name, '扣款', '-100.50 USDT'])
            ->assertSee(now()->format('Y-m-d H:i:s'))
            ->assertDontSee('<b>literal</b>', escape: false)
            ->assertDontSee('另一用户的订单')
            ->assertDontSee('历史独立解冻')
            ->assertDontSee('+100.50 USDT');

        $release = app(ReleaseAdminReservationFundOrderService::class);
        $release->handle($admin, $user->id, $order->id);

        $this->get('/me/orders')
            ->assertOk()
            ->assertSee('链上预约资金')
            ->assertSee('较新的扣款')
            ->assertDontSee($name)
            ->assertDontSee('-100.50 USDT');

        $release->handle($admin, $user->id, $newerOrder->id);

        $this->get('/me/orders')
            ->assertOk()
            ->assertDontSee('链上预约资金')
            ->assertDontSee('较新的扣款');
    }
}
