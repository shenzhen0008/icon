<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_commission_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('settlement_id')->constrained('daily_settlements')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('base_profit', 16, 2);
            $table->decimal('commission_rate', 8, 4);
            $table->decimal('commission_amount', 16, 2);
            $table->string('status', 20);
            $table->timestamp('granted_at')->nullable();
            $table->text('failed_reason')->nullable();
            $table->timestamps();

            $table->unique(['settlement_id', 'level'], 'referral_commission_settlement_level_unique');
            $table->index(['referrer_id', 'granted_at'], 'referral_commission_referrer_granted_index');
            $table->index(['referred_user_id', 'granted_at'], 'referral_commission_referred_granted_index');
            $table->index(['status', 'id'], 'referral_commission_status_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commission_records');
    }
};
