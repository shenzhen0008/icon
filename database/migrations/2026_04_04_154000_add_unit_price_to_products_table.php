<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'unit_price')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->decimal('unit_price', 16, 2)->default(1000)->after('code');
            });
        }

        DB::table('products')
            ->where('unit_price', 1000)
            ->update(['unit_price' => DB::raw('min_balance_required')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'unit_price')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('unit_price');
            });
        }
    }
};
