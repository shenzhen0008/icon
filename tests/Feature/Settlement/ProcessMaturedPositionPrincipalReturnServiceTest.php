<?php

namespace Tests\Feature\Settlement;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Settlement\Services\ProcessMaturedPositionPrincipalReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessMaturedPositionPrincipalReturnServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_matured_position_returns_principal_and_closes_position(): void
    {
        $user = User::factory()->create([
            'balance' => 100,
        ]);

        $product = Product::query()->create([
            'name' => 'Matured Product',
            'code' => 'MAT-P',
            'unit_price' => 1000,
            'is_active' => true,
            'cycle_days' => 2,
        ]);

        $position = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => '2026-04-01 08:00:00',
        ]);

        $stats = app(ProcessMaturedPositionPrincipalReturnService::class)->handle('2026-04-03');

        $this->assertSame(1, $stats['scanned']);
        $this->assertSame(1, $stats['returned']);
        $this->assertSame(0, $stats['skipped']);
        $this->assertSame(0, $stats['failed']);

        $user->refresh();
        $this->assertSame('1100.00', number_format((float) $user->balance, 2, '.', ''));

        $position->refresh();
        $this->assertSame('closed', $position->status);
        $this->assertNotNull($position->closed_at);

        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'principal_return_credit',
            'amount' => 1000,
            'before_balance' => 100,
            'after_balance' => 1100,
            'biz_ref_type' => 'position',
            'biz_ref_id' => (string) $position->id,
        ]);
    }

    public function test_unmatured_position_is_skipped(): void
    {
        $user = User::factory()->create([
            'balance' => 100,
        ]);

        $product = Product::query()->create([
            'name' => 'Unmatured Product',
            'code' => 'UNMAT-P',
            'unit_price' => 1000,
            'is_active' => true,
            'cycle_days' => 2,
        ]);

        $position = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => '2026-04-02 08:00:00',
        ]);

        $stats = app(ProcessMaturedPositionPrincipalReturnService::class)->handle('2026-04-03');

        $this->assertSame(1, $stats['scanned']);
        $this->assertSame(0, $stats['returned']);
        $this->assertSame(1, $stats['skipped']);
        $this->assertSame(0, $stats['failed']);

        $position->refresh();
        $this->assertSame('open', $position->status);

        $user->refresh();
        $this->assertSame('100.00', number_format((float) $user->balance, 2, '.', ''));

        $this->assertDatabaseMissing('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'principal_return_credit',
            'biz_ref_type' => 'position',
            'biz_ref_id' => (string) $position->id,
        ]);
    }

    public function test_matured_principal_return_is_idempotent(): void
    {
        $user = User::factory()->create([
            'balance' => 100,
        ]);

        $product = Product::query()->create([
            'name' => 'Idempotent Product',
            'code' => 'IDM-P',
            'unit_price' => 1000,
            'is_active' => true,
            'cycle_days' => 1,
        ]);

        $position = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => '2026-04-01 08:00:00',
        ]);

        $service = app(ProcessMaturedPositionPrincipalReturnService::class);
        $service->handle('2026-04-02');
        $secondRunStats = $service->handle('2026-04-02');

        $this->assertSame(0, $secondRunStats['returned']);
        $this->assertDatabaseCount('balance_ledgers', 1);

        $position->refresh();
        $this->assertSame('closed', $position->status);
    }
}
