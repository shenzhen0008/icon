<?php

namespace Tests\Feature\Reservation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReservationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_table_has_trade_mode_and_reservation_table_exists(): void
    {
        $this->assertTrue(Schema::hasColumn('products', 'trade_mode'));

        $this->assertTrue(Schema::hasTable('product_reservations'));
        $this->assertTrue(Schema::hasColumns('product_reservations', [
            'id',
            'user_id',
            'product_id',
            'amount_usdt',
            'status',
            'reviewed_by',
            'reviewed_at',
            'review_note',
            'approved_at',
            'converted_at',
            'converted_position_id',
            'created_at',
            'updated_at',
        ]));
    }
}
