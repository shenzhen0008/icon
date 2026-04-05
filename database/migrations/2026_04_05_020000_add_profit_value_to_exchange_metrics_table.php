<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('exchange_metrics', 'profit_value')) {
            Schema::table('exchange_metrics', function (Blueprint $table): void {
                $table->decimal('profit_value', 16, 2)->default(0)->after('total_value');
            });
        }

        DB::table('exchange_metrics')
            ->where('profit_value', 0)
            ->update([
                'profit_value' => DB::raw('ROUND(total_value * 20, 2)'),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('exchange_metrics', 'profit_value')) {
            Schema::table('exchange_metrics', function (Blueprint $table): void {
                $table->dropColumn('profit_value');
            });
        }
    }
};
