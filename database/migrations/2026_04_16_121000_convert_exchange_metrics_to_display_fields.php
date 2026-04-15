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
            $table->string('display_profit_value', 64)->default('0.00')->after('exchange_name');
            $table->string('display_btc_volume', 64)->default('$0.00')->after('display_profit_value');
            $table->string('display_btc_liquidity', 64)->default('0')->after('display_btc_volume');
            $table->string('display_eth_volume', 64)->default('$0.00')->after('display_btc_liquidity');
            $table->string('display_eth_liquidity', 64)->default('0')->after('display_eth_volume');
            $table->string('display_updated_at', 64)->default('--')->after('display_eth_liquidity');
        });

        DB::table('exchange_metrics')
            ->orderBy('id')
            ->get()
            ->each(function (object $metric): void {
                DB::table('exchange_metrics')
                    ->where('id', $metric->id)
                    ->update([
                        'display_profit_value' => number_format((float) $metric->profit_value, 2, '.', ','),
                        'display_btc_volume' => '$'.number_format((float) $metric->btc_value, 2, '.', ','),
                        'display_btc_liquidity' => (string) $metric->btc_liquidity,
                        'display_eth_volume' => '$'.number_format((float) $metric->eth_value, 2, '.', ','),
                        'display_eth_liquidity' => (string) $metric->eth_liquidity,
                        'display_updated_at' => $metric->updated_at ?? '--',
                    ]);
            });

        Schema::table('exchange_metrics', function (Blueprint $table): void {
            $table->dropColumn([
                'btc_value',
                'btc_liquidity',
                'eth_value',
                'eth_liquidity',
                'total_value',
                'profit_value',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('exchange_metrics', function (Blueprint $table): void {
            $table->decimal('btc_value', 20, 8)->default(0)->after('exchange_name');
            $table->unsignedInteger('btc_liquidity')->default(0)->after('btc_value');
            $table->decimal('eth_value', 20, 8)->default(0)->after('btc_liquidity');
            $table->unsignedInteger('eth_liquidity')->default(0)->after('eth_value');
            $table->decimal('total_value', 20, 8)->default(0)->after('eth_liquidity');
            $table->decimal('profit_value', 16, 2)->default(0)->after('total_value');
        });

        DB::table('exchange_metrics')
            ->orderBy('id')
            ->get()
            ->each(function (object $metric): void {
                $btcValue = (float) preg_replace('/[^0-9.\-]/', '', (string) $metric->display_btc_volume);
                $ethValue = (float) preg_replace('/[^0-9.\-]/', '', (string) $metric->display_eth_volume);

                DB::table('exchange_metrics')
                    ->where('id', $metric->id)
                    ->update([
                        'btc_value' => $btcValue,
                        'btc_liquidity' => (int) preg_replace('/[^0-9\-]/', '', (string) $metric->display_btc_liquidity),
                        'eth_value' => $ethValue,
                        'eth_liquidity' => (int) preg_replace('/[^0-9\-]/', '', (string) $metric->display_eth_liquidity),
                        'total_value' => round($btcValue + $ethValue, 8),
                        'profit_value' => (float) preg_replace('/[^0-9.\-]/', '', (string) $metric->display_profit_value),
                    ]);
            });

        Schema::table('exchange_metrics', function (Blueprint $table): void {
            $table->dropColumn([
                'display_profit_value',
                'display_btc_volume',
                'display_btc_liquidity',
                'display_eth_volume',
                'display_eth_liquidity',
                'display_updated_at',
            ]);
        });
    }
};
