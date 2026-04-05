<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchange_metrics', function (Blueprint $table): void {
            if (! Schema::hasColumn('exchange_metrics', 'btc_liquidity')) {
                $table->unsignedInteger('btc_liquidity')->default(0)->after('btc_value');
            }
            if (! Schema::hasColumn('exchange_metrics', 'eth_liquidity')) {
                $table->unsignedInteger('eth_liquidity')->default(0)->after('eth_value');
            }
        });

        DB::statement("
            UPDATE exchange_metrics
            SET
                btc_liquidity = CASE exchange_code
                    WHEN 'binance' THEN 947
                    WHEN 'huobi' THEN 936
                    WHEN 'gate' THEN 918
                    WHEN 'okx' THEN 952
                    WHEN 'kucoin' THEN 905
                    WHEN 'kraken' THEN 927
                    ELSE 900
                END,
                eth_liquidity = CASE exchange_code
                    WHEN 'binance' THEN 999
                    WHEN 'huobi' THEN 982
                    WHEN 'gate' THEN 961
                    WHEN 'okx' THEN 991
                    WHEN 'kucoin' THEN 948
                    WHEN 'kraken' THEN 965
                    ELSE 950
                END
        ");
    }

    public function down(): void
    {
        Schema::table('exchange_metrics', function (Blueprint $table): void {
            if (Schema::hasColumn('exchange_metrics', 'btc_liquidity')) {
                $table->dropColumn('btc_liquidity');
            }
            if (Schema::hasColumn('exchange_metrics', 'eth_liquidity')) {
                $table->dropColumn('eth_liquidity');
            }
        });
    }
};
