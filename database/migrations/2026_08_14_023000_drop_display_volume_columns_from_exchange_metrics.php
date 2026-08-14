<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('exchange_metrics', 'display_btc_volume')) {
            return;
        }

        Schema::table('exchange_metrics', function (Blueprint $table): void {
            $table->dropColumn([
                'display_btc_volume',
                'display_eth_volume',
            ]);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('exchange_metrics', 'display_btc_volume')) {
            return;
        }

        Schema::table('exchange_metrics', function (Blueprint $table): void {
            $table->string('display_btc_volume', 64)->default('$0.00')->after('exchange_name');
            $table->string('display_eth_volume', 64)->default('$0.00')->after('display_btc_liquidity');
        });
    }
};
