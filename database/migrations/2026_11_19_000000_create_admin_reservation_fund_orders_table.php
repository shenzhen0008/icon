<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_reservation_fund_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('name');
            $table->decimal('amount', 16, 2);
            $table->timestamps();
            $table->index(['user_id', 'created_at', 'id'], 'reservation_fund_orders_user_time_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_reservation_fund_orders');
    }
};
