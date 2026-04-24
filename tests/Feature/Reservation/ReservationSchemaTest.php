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
        $this->assertTrue(Schema::hasColumn('products', 'purchase_limit_count'));
        $this->assertTrue(Schema::hasColumn('users', 'invite_code'));
        $this->assertTrue(Schema::hasColumn('users', 'referrer_id'));

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

        $this->assertTrue(Schema::hasTable('referral_commission_records'));
        $this->assertTrue(Schema::hasColumns('referral_commission_records', [
            'id',
            'settlement_id',
            'level',
            'referrer_id',
            'referred_user_id',
            'base_profit',
            'commission_rate',
            'commission_amount',
            'status',
            'granted_at',
            'failed_reason',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('referral_commission_settings'));
        $this->assertTrue(Schema::hasColumns('referral_commission_settings', [
            'id',
            'level_1_rate',
            'level_2_rate',
            'is_active',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('savings_yield_settings'));
        $this->assertTrue(Schema::hasColumns('savings_yield_settings', [
            'id',
            'daily_rate',
            'is_active',
            'created_at',
            'updated_at',
        ]));
    }
}
