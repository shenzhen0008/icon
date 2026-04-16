<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $allowedAssetCodes = (array) config('recharge.allowed_receive_assets', ['USDT', 'USDC', 'BTC', 'ETH']);

        DB::table('recharge_receivers')
            ->whereNotIn('asset_code', $allowedAssetCodes)
            ->delete();
    }

    public function down(): void
    {
        // Data cleanup migration is intentionally irreversible.
    }
};

