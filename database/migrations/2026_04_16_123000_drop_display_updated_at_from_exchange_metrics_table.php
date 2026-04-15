<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchange_metrics', function (Blueprint $table): void {
            if (Schema::hasColumn('exchange_metrics', 'display_updated_at')) {
                $table->dropColumn('display_updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exchange_metrics', function (Blueprint $table): void {
            if (! Schema::hasColumn('exchange_metrics', 'display_updated_at')) {
                $table->string('display_updated_at', 64)->default('--')->after('display_eth_liquidity');
            }
        });
    }
};
