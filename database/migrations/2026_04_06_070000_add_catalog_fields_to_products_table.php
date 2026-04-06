<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedInteger('purchase_limit')->nullable()->after('unit_price');
            $table->decimal('limit_min_usdt', 16, 2)->nullable()->after('purchase_limit');
            $table->decimal('limit_max_usdt', 16, 2)->nullable()->after('limit_min_usdt');
            $table->decimal('rate_min_percent', 8, 2)->nullable()->after('limit_max_usdt');
            $table->decimal('rate_max_percent', 8, 2)->nullable()->after('rate_min_percent');
            $table->unsignedInteger('cycle_days')->nullable()->after('rate_max_percent');
            $table->string('buy_button_text', 50)->default('立即购买')->after('cycle_days');
            $table->string('product_icon_path')->nullable()->after('buy_button_text');
            $table->json('symbol_icon_paths')->nullable()->after('product_icon_path');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'purchase_limit',
                'limit_min_usdt',
                'limit_max_usdt',
                'rate_min_percent',
                'rate_max_percent',
                'cycle_days',
                'buy_button_text',
                'product_icon_path',
                'symbol_icon_paths',
            ]);
        });
    }
};
