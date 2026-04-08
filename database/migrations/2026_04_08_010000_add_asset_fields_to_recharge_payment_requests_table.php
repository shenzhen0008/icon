<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recharge_payment_requests', function (Blueprint $table): void {
            $table->string('asset_code', 20)->default('USDT')->after('contact_account');
            $table->string('receipt_address', 255)->default('')->after('network');
            $table->index(['asset_code', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('recharge_payment_requests', function (Blueprint $table): void {
            $table->dropIndex('recharge_payment_requests_asset_code_submitted_at_index');
            $table->dropColumn(['asset_code', 'receipt_address']);
        });
    }
};
