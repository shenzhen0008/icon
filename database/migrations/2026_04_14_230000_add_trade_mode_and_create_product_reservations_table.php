<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('trade_mode', 20)
                ->default('direct')
                ->after('is_active');

            $table->index(
                ['is_active', 'trade_mode', 'sort', 'id'],
                'products_active_mode_sort_id_index'
            );
        });

        Schema::create('product_reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('shares');
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->timestamps();

            $table->index(
                ['user_id', 'status', 'created_at'],
                'reservations_user_status_created_index'
            );
            $table->index(
                ['product_id', 'status', 'created_at'],
                'reservations_product_status_created_index'
            );
            $table->index(
                ['status', 'created_at'],
                'reservations_status_created_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reservations');

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_active_mode_sort_id_index');
            $table->dropColumn('trade_mode');
        });
    }
};
