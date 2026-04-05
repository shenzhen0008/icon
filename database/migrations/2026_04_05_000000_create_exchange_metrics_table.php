<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_metrics', function (Blueprint $table): void {
            $table->id();
            $table->string('exchange_code', 32)->unique();
            $table->string('exchange_name', 64);
            $table->decimal('btc_value', 20, 8)->default(0);
            $table->unsignedInteger('btc_liquidity')->default(0);
            $table->decimal('eth_value', 20, 8)->default(0);
            $table->unsignedInteger('eth_liquidity')->default(0);
            $table->decimal('total_value', 20, 8)->default(0);
            $table->decimal('profit_value', 16, 2)->default(0);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort']);
        });

        DB::table('exchange_metrics')->insert([
            ['exchange_code' => 'binance', 'exchange_name' => 'Binance', 'sort' => 10, 'is_active' => true, 'btc_value' => 0, 'btc_liquidity' => 947, 'eth_value' => 0, 'eth_liquidity' => 999, 'total_value' => 0, 'profit_value' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['exchange_code' => 'huobi', 'exchange_name' => 'Huobi', 'sort' => 20, 'is_active' => true, 'btc_value' => 0, 'btc_liquidity' => 936, 'eth_value' => 0, 'eth_liquidity' => 982, 'total_value' => 0, 'profit_value' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['exchange_code' => 'gate', 'exchange_name' => 'Gate', 'sort' => 30, 'is_active' => true, 'btc_value' => 0, 'btc_liquidity' => 918, 'eth_value' => 0, 'eth_liquidity' => 961, 'total_value' => 0, 'profit_value' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['exchange_code' => 'okx', 'exchange_name' => 'OKX', 'sort' => 40, 'is_active' => true, 'btc_value' => 0, 'btc_liquidity' => 952, 'eth_value' => 0, 'eth_liquidity' => 991, 'total_value' => 0, 'profit_value' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['exchange_code' => 'kucoin', 'exchange_name' => 'KuCoin', 'sort' => 50, 'is_active' => true, 'btc_value' => 0, 'btc_liquidity' => 905, 'eth_value' => 0, 'eth_liquidity' => 948, 'total_value' => 0, 'profit_value' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['exchange_code' => 'kraken', 'exchange_name' => 'Kraken', 'sort' => 60, 'is_active' => true, 'btc_value' => 0, 'btc_liquidity' => 927, 'eth_value' => 0, 'eth_liquidity' => 965, 'total_value' => 0, 'profit_value' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_metrics');
    }
};
