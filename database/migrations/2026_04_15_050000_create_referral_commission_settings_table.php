<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_commission_settings', function (Blueprint $table): void {
            $table->id();
            $table->decimal('level_1_rate', 8, 4)->default(0.05);
            $table->decimal('level_2_rate', 8, 4)->default(0.02);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('referral_commission_settings')->insert([
            'id' => 1,
            'level_1_rate' => '0.0500',
            'level_2_rate' => '0.0200',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commission_settings');
    }
};
