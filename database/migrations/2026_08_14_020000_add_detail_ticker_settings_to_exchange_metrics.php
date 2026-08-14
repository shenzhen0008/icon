<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('home_display_settings', 'shared_exchange_volume_step_seconds')) {
            Schema::table('home_display_settings', function (Blueprint $table): void {
                $table->dropColumn([
                    'shared_exchange_volume_step_seconds',
                    'shared_exchange_volume_min_delta',
                    'shared_exchange_volume_max_delta',
                    'shared_exchange_liquidity_step_seconds',
                    'shared_exchange_liquidity_min_delta',
                    'shared_exchange_liquidity_max_delta',
                ]);
            });
        }

        Schema::table('exchange_metrics', function (Blueprint $table): void {
            $table->unsignedInteger('liquidity_step_seconds')->default(3)->after('display_eth_liquidity');
            $table->decimal('liquidity_min_delta', 10, 2)->default(-5)->after('liquidity_step_seconds');
            $table->decimal('liquidity_max_delta', 10, 2)->default(10)->after('liquidity_min_delta');
        });
    }

    public function down(): void
    {
        Schema::table('exchange_metrics', function (Blueprint $table): void {
            $table->dropColumn([
                'liquidity_step_seconds',
                'liquidity_min_delta',
                'liquidity_max_delta',
            ]);
        });

        if (! Schema::hasColumn('home_display_settings', 'shared_exchange_volume_step_seconds')) {
            Schema::table('home_display_settings', function (Blueprint $table): void {
                $table->unsignedInteger('shared_exchange_volume_step_seconds')->default(3)->after('shared_exchange_profit_max_delta');
                $table->decimal('shared_exchange_volume_min_delta', 10, 2)->default(0)->after('shared_exchange_volume_step_seconds');
                $table->decimal('shared_exchange_volume_max_delta', 10, 2)->default(0.10)->after('shared_exchange_volume_min_delta');
                $table->unsignedInteger('shared_exchange_liquidity_step_seconds')->default(3)->after('shared_exchange_volume_max_delta');
                $table->decimal('shared_exchange_liquidity_min_delta', 10, 2)->default(-5)->after('shared_exchange_liquidity_step_seconds');
                $table->decimal('shared_exchange_liquidity_max_delta', 10, 2)->default(10)->after('shared_exchange_liquidity_min_delta');
            });
        }
    }
};
