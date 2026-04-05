<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'balance')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->decimal('balance', 16, 2)->default(0)->after('password');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'balance')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('balance');
            });
        }
    }
};
