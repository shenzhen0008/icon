<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balance_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 32);
            $table->decimal('amount', 16, 2);
            $table->decimal('before_balance', 16, 2);
            $table->decimal('after_balance', 16, 2);
            $table->string('biz_ref_type', 32);
            $table->string('biz_ref_id', 64);
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['user_id', 'occurred_at']);
            $table->unique(['user_id', 'type', 'biz_ref_type', 'biz_ref_id'], 'balance_ledgers_biz_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_ledgers');
    }
};
