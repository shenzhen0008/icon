<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_display_settings', function (Blueprint $table): void {
            $table->decimal('shared_exchange_btc_volume_multiplier', 12, 4)->default(1)->after('shared_exchange_profit_max_delta');
            $table->decimal('shared_exchange_eth_volume_multiplier', 12, 4)->default(1)->after('shared_exchange_btc_volume_multiplier');
        });
    }

    public function down(): void
    {
        Schema::table('home_display_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'shared_exchange_btc_volume_multiplier',
                'shared_exchange_eth_volume_multiplier',
            ]);
        });
    }
};
