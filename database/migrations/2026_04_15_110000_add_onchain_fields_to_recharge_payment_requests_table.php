<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recharge_payment_requests', function (Blueprint $table): void {
            $table->string('channel', 20)->default('manual_transfer')->after('receipt_image_path');
            $table->string('tx_hash', 100)->nullable()->after('channel');
            $table->string('chain_id', 32)->nullable()->after('tx_hash');
            $table->string('from_address', 64)->nullable()->after('chain_id');
            $table->string('to_address', 64)->nullable()->after('from_address');
            $table->timestamp('tx_submitted_at')->nullable()->after('to_address');

            $table->index(['channel', 'status', 'submitted_at'], 'recharge_payment_requests_channel_status_submitted_at_index');
            $table->unique(['channel', 'tx_hash'], 'recharge_payment_requests_channel_tx_hash_unique');
        });
    }

    public function down(): void
    {
        Schema::table('recharge_payment_requests', function (Blueprint $table): void {
            $table->dropUnique('recharge_payment_requests_channel_tx_hash_unique');
            $table->dropIndex('recharge_payment_requests_channel_status_submitted_at_index');
            $table->dropColumn([
                'channel',
                'tx_hash',
                'chain_id',
                'from_address',
                'to_address',
                'tx_submitted_at',
            ]);
        });
    }
};
