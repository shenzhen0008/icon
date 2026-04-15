<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_display_settings', function (Blueprint $table): void {
            $table->unsignedInteger('summary_people_step_seconds')->default(3)->after('summary_people_count');
            $table->decimal('summary_people_min_delta', 10, 2)->default(0)->after('summary_people_step_seconds');
            $table->decimal('summary_people_max_delta', 10, 2)->default(0)->after('summary_people_min_delta');
            $table->timestamp('summary_people_last_tick_at')->nullable()->after('summary_people_max_delta');

            $table->unsignedInteger('summary_profit_step_seconds')->default(3)->after('summary_total_profit');
            $table->decimal('summary_profit_min_delta', 12, 2)->default(0)->after('summary_profit_step_seconds');
            $table->decimal('summary_profit_max_delta', 12, 2)->default(0)->after('summary_profit_min_delta');
            $table->timestamp('summary_profit_last_tick_at')->nullable()->after('summary_profit_max_delta');
        });
    }

    public function down(): void
    {
        Schema::table('home_display_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'summary_people_step_seconds',
                'summary_people_min_delta',
                'summary_people_max_delta',
                'summary_people_last_tick_at',
                'summary_profit_step_seconds',
                'summary_profit_min_delta',
                'summary_profit_max_delta',
                'summary_profit_last_tick_at',
            ]);
        });
    }
};
