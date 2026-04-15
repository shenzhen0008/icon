<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_display_settings', function (Blueprint $table): void {
            $table->decimal('shared_exchange_profit_base_value', 12, 2)->default(0)->after('summary_profit_last_tick_at');
            $table->unsignedInteger('shared_exchange_profit_step_seconds')->default(3)->after('shared_exchange_profit_base_value');
            $table->decimal('shared_exchange_profit_min_delta', 10, 2)->default(0)->after('shared_exchange_profit_step_seconds');
            $table->decimal('shared_exchange_profit_max_delta', 10, 2)->default(0)->after('shared_exchange_profit_min_delta');
        });

        if (Schema::hasTable('exchange_metrics') && Schema::hasColumn('exchange_metrics', 'display_profit_value')) {
            $firstProfit = DB::table('exchange_metrics')
                ->orderBy('sort')
                ->orderBy('id')
                ->value('display_profit_value');

            $baseValue = (float) preg_replace('/[^0-9.\-]/', '', (string) $firstProfit);

            DB::table('home_display_settings')
                ->where('id', 1)
                ->update([
                    'shared_exchange_profit_base_value' => number_format($baseValue, 2, '.', ''),
                    'shared_exchange_profit_step_seconds' => 3,
                    'shared_exchange_profit_min_delta' => '0.00',
                    'shared_exchange_profit_max_delta' => '0.00',
                ]);
        }

        Schema::table('exchange_metrics', function (Blueprint $table): void {
            if (Schema::hasColumn('exchange_metrics', 'display_profit_value')) {
                $table->dropColumn('display_profit_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exchange_metrics', function (Blueprint $table): void {
            if (! Schema::hasColumn('exchange_metrics', 'display_profit_value')) {
                $table->string('display_profit_value', 64)->default('0.00')->after('exchange_name');
            }
        });

        Schema::table('home_display_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'shared_exchange_profit_base_value',
                'shared_exchange_profit_step_seconds',
                'shared_exchange_profit_min_delta',
                'shared_exchange_profit_max_delta',
            ]);
        });
    }
};
