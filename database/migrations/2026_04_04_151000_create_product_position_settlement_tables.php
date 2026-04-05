<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->decimal('min_balance_required', 16, 2);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort']);
        });

        Schema::create('product_daily_returns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->date('return_date');
            $table->decimal('rate', 8, 4);
            $table->timestamps();

            $table->unique(['product_id', 'return_date'], 'product_date_unique');
            $table->index('return_date');
        });

        Schema::create('positions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('principal', 16, 2);
            $table->string('status', 20)->default('open');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['product_id', 'status']);
        });

        Schema::create('daily_settlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->date('settlement_date');
            $table->decimal('rate', 8, 4);
            $table->decimal('profit', 16, 2);
            $table->timestamps();

            $table->unique(['position_id', 'settlement_date'], 'position_date_settlement_unique');
            $table->index(['user_id', 'settlement_date']);
            $table->index(['product_id', 'settlement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_settlements');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('product_daily_returns');
        Schema::dropIfExists('products');
    }
};
