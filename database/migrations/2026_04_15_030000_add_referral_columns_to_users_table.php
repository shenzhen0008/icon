<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'invite_code')) {
                $table->string('invite_code', 32)->nullable()->unique()->after('balance');
            }

            if (! Schema::hasColumn('users', 'referrer_id')) {
                $table->foreignId('referrer_id')
                    ->nullable()
                    ->after('invite_code')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'referrer_id')) {
                $table->dropConstrainedForeignId('referrer_id');
            }

            if (Schema::hasColumn('users', 'invite_code')) {
                $table->dropUnique('users_invite_code_unique');
                $table->dropColumn('invite_code');
            }
        });
    }
};
