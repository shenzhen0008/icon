<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Modules\Balance\Models\AdminReservationFundOrder as Order;
use App\Modules\Balance\Services\CreateAdminReservationFundOrderService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class UserLedgerActionTest extends AdminPanelTestCase
{
    use RefreshDatabase;

    public function test_admin_can_release_an_existing_debit_and_view_the_ledger(): void
    {
        $user = User::factory()->create(['balance' => '50.00']);
        $action = TestAction::make('create_reservation_fund_order')->table($user);

        $table = Livewire::test(ListUsers::class)
            ->callAction($action, data: [
                'name' => '预约扣款 🌟', 'amount' => '100.50',
            ])
            ->assertHasNoErrors();

        $order = Order::query()->sole();
        $table->callAction(TestAction::make('release_reservation_fund_order')->table($user), data: [
            'order_id' => $order->id,
        ])->assertHasNoErrors();

        $this->assertDatabaseCount('admin_reservation_fund_orders', 1);
        $this->assertSame('100.50', $user->refresh()->balance);
        $this->assertDatabaseHas('admin_reservation_fund_orders', [
            'user_id' => $user->id, 'name' => '预约扣款 🌟', 'amount' => '100.50',
        ]);

        $table->mountAction(TestAction::make('view_bill')->table($user));
        $bill = $table->instance()->getMountedAction()->getModalContent()->render();
        $this->assertStringContainsString('预约扣款 🌟', $bill);
        $this->assertStringContainsString('+100.50', $bill);
    }

    public function test_admin_form_rejects_invalid_amount(): void
    {
        $user = User::factory()->create();

        Livewire::test(ListUsers::class)
            ->callAction(TestAction::make('create_reservation_fund_order')->table($user), data: [
                'type' => Order::DEBIT, 'name' => '扣款', 'amount' => '0',
            ])
            ->assertHasErrors();

        $this->assertDatabaseCount('admin_reservation_fund_orders', 0);
        $this->assertDatabaseCount('balance_ledgers', 0);
    }

    public function test_non_admin_cannot_access_the_user_table_or_create_an_order(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/users')->assertRedirect('/admin/login');

        try {
            app(CreateAdminReservationFundOrderService::class)->handle($user, $user->id, [
                'type' => Order::DEBIT, 'name' => '扣款', 'amount' => '1.00',
            ]);
            $this->fail('The non-admin should have been denied.');
        } catch (AuthorizationException) {
            $this->assertDatabaseCount('admin_reservation_fund_orders', 0);
            $this->assertDatabaseCount('balance_ledgers', 0);
        }
    }
}
