<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'min_balance_required')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('min_balance_required');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'min_balance_required')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->decimal('min_balance_required', 16, 2)->default(0)->after('unit_price');
            });
        }
    }
};
