<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_yield_settings', function (Blueprint $table): void {
            $table->id();
            $table->decimal('daily_rate', 8, 4)->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        DB::table('savings_yield_settings')->insert([
            'id' => 1,
            'daily_rate' => '0.0000',
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_yield_settings');
    }
};
