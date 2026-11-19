<?php

namespace Tests\Feature\Balance;

use App\Models\User;
use App\Modules\Balance\Models\AdminReservationFundOrder as Order;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Balance\Services\CreateAdminReservationFundOrderService;
use App\Modules\Balance\Services\ReleaseAdminReservationFundOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class AdminReservationFundOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_order_and_ledger_amounts_survive_a_clamped_debit_and_release(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['balance' => '200.00']);
        $service = app(CreateAdminReservationFundOrderService::class);

        foreach ([
            [Order::DEBIT, '1000.00', '-1000.00', '200.00', '0.00'],
            [Order::DEBIT, '1.00', '-1.00', '0.00', '0.00'],
        ] as [$type, $amount, $signed, $before, $after]) {
            $order = $service->handle($admin, $user->id, compact('type', 'amount') + ['name' => '预约资金']);
            $this->assertSame($amount, $order->refresh()->amount);
            $this->assertSame($signed, $order->signedAmount());
            $this->assertSame($after, $user->refresh()->balance);
            $ledger = BalanceLedger::query()->where('biz_ref_type', Order::REFERENCE_TYPE)
                ->where('biz_ref_id', (string) $order->id)->sole();
            $this->assertSame(ltrim($signed, '+'), $ledger->amount);
            $this->assertSame($before, $ledger->before_balance);
            $this->assertSame($after, $ledger->after_balance);
            $this->assertTrue($ledger->occurred_at->equalTo($order->created_at));
        }
        $original = Order::query()->oldest('id')->firstOrFail();
        $release = app(ReleaseAdminReservationFundOrderService::class);
        $release->handle($admin, $user->id, $original->id);

        $this->assertSame('1000.00', $user->refresh()->balance);
        $this->assertSame('1000.00', $original->releaseLedger()->sole()->amount);
        $this->assertDatabaseCount('admin_reservation_fund_orders', 2);

        foreach ([$user->id, User::factory()->create()->id] as $targetId) {
            try {
                $release->handle($admin, $targetId, $original->id);
                $this->fail('An already released or unrelated order must be rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('order_id', $exception->errors());
            }
        }

        $this->assertSame('1000.00', $user->refresh()->balance);
        $this->assertDatabaseCount('balance_ledgers', 3);
    }

    #[DataProvider('invalidAmounts')]
    public function test_invalid_debit_amount_keeps_all_records_unchanged(string $amount): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['balance' => '0.01']);

        try {
            app(CreateAdminReservationFundOrderService::class)->handle($admin, $user->id, [
                'type' => Order::DEBIT, 'name' => '扣款', 'amount' => $amount,
            ]);
            $this->fail('The amount should have been rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('amount', $exception->errors());
        }

        $this->assertSame('0.01', $user->refresh()->balance);
        $this->assertDatabaseCount('admin_reservation_fund_orders', 0);
        $this->assertDatabaseCount('balance_ledgers', 0);
    }

    public static function invalidAmounts(): array
    {
        return [['0'], ['-1'], ['1.001'], ['1e3'], ['100000000000000'], ['99999999999999.999']];
    }

    public function test_release_that_would_overflow_balance_leaves_order_available(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['balance' => '99999999999999.99']);
        $order = Order::query()->create([
            'user_id' => $user->id, 'type' => Order::DEBIT, 'name' => '扣款', 'amount' => '0.01',
        ]);

        try {
            app(ReleaseAdminReservationFundOrderService::class)->handle($admin, $user->id, $order->id);
            $this->fail('The release should have been rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('order_id', $exception->errors());
        }

        $this->assertSame('99999999999999.99', $user->refresh()->balance);
        $this->assertFalse($order->releaseLedger()->exists());
        $this->assertDatabaseCount('admin_reservation_fund_orders', 1);
    }

    public function test_ledger_write_failure_rolls_back_order_and_balance(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['balance' => '10.00']);
        Event::listen('eloquent.creating: '.BalanceLedger::class, function (): never {
            throw new RuntimeException('Ledger write failed.');
        });

        try {
            app(CreateAdminReservationFundOrderService::class)->handle($admin, $user->id, [
                'type' => Order::DEBIT, 'name' => '扣款', 'amount' => '1.00',
            ]);
            $this->fail('The ledger write should have failed.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Ledger write failed.', $exception->getMessage());
        }

        $this->assertSame('10.00', $user->refresh()->balance);
        $this->assertDatabaseCount('admin_reservation_fund_orders', 0);
        $this->assertDatabaseCount('balance_ledgers', 0);
    }
}
