<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reservations', function (Blueprint $table): void {
            $table->decimal('amount_usdt', 16, 2)->default(0)->after('product_id');
        });

        DB::statement('UPDATE product_reservations pr JOIN products p ON p.id = pr.product_id SET pr.amount_usdt = ROUND(pr.shares * p.unit_price, 2)');

        Schema::table('product_reservations', function (Blueprint $table): void {
            $table->dropColumn('shares');
        });
    }

    public function down(): void
    {
        Schema::table('product_reservations', function (Blueprint $table): void {
            $table->unsignedInteger('shares')->default(1)->after('product_id');
        });

        DB::statement('UPDATE product_reservations pr JOIN products p ON p.id = pr.product_id SET pr.shares = GREATEST(1, ROUND(pr.amount_usdt / NULLIF(p.unit_price, 0), 0))');

        Schema::table('product_reservations', function (Blueprint $table): void {
            $table->dropColumn('amount_usdt');
        });
    }
};
