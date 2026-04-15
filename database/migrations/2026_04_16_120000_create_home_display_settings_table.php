<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_display_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('summary_people_count', 64)->default('0');
            $table->string('summary_total_profit', 64)->default('0.00 USDT');
            $table->timestamps();
        });

        $metrics = Schema::hasTable('exchange_metrics')
            ? DB::table('exchange_metrics')->get(['btc_liquidity', 'eth_liquidity', 'profit_value'])
            : collect();

        $summaryPeopleCount = number_format(
            (int) $metrics->sum(fn (object $metric): int => (int) $metric->btc_liquidity + (int) $metric->eth_liquidity),
            0,
            '.',
            ','
        );

        $summaryTotalProfit = number_format(
            (float) $metrics->sum(fn (object $metric): float => (float) $metric->profit_value),
            2,
            '.',
            ','
        ).' USDT';

        DB::table('home_display_settings')->insert([
            'id' => 1,
            'summary_people_count' => $summaryPeopleCount,
            'summary_total_profit' => $summaryTotalProfit,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('home_display_settings');
    }
};
