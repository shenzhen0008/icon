<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('exchange_metrics', 'volume_step_seconds')) {
            return;
        }

        Schema::table('exchange_metrics', function (Blueprint $table): void {
            $table->dropColumn([
                'volume_step_seconds',
                'volume_min_delta',
                'volume_max_delta',
            ]);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('exchange_metrics', 'volume_step_seconds')) {
            return;
        }

        Schema::table('exchange_metrics', function (Blueprint $table): void {
            $table->unsignedInteger('volume_step_seconds')->default(3)->after('display_eth_liquidity');
            $table->decimal('volume_min_delta', 10, 2)->default(0)->after('volume_step_seconds');
            $table->decimal('volume_max_delta', 10, 2)->default(0.10)->after('volume_min_delta');
        });
    }
};
