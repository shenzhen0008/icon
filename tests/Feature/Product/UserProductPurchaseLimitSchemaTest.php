<?php

namespace Tests\Feature\Product;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserProductPurchaseLimitSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_product_purchase_limits_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('user_product_purchase_limits'));
        $this->assertTrue(Schema::hasColumns('user_product_purchase_limits', [
            'id',
            'user_id',
            'product_id',
            'allowed_purchase_limit',
            'updated_by',
            'created_at',
            'updated_at',
        ]));
    }
}

