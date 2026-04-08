<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recharge_payment_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('contact_account', 64);
            $table->decimal('payment_amount', 16, 2);
            $table->string('currency', 10)->default('USDT');
            $table->string('network', 20)->default('TRC20');
            $table->string('receipt_image_path', 255);
            $table->string('status', 20)->default('pending');
            $table->text('user_note')->nullable();
            $table->timestamp('submitted_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'submitted_at']);
            $table->index(['user_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recharge_payment_requests');
    }
};
