<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('popup_campaigns')) {
            return;
        }

        if (Schema::hasColumn('popup_campaigns', 'title')) {
            Schema::table('popup_campaigns', function (Blueprint $table): void {
                $table->dropColumn('title');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('popup_campaigns')) {
            return;
        }

        if (! Schema::hasColumn('popup_campaigns', 'title')) {
            Schema::table('popup_campaigns', function (Blueprint $table): void {
                $table->string('title', 120)->nullable()->after('id');
            });
        }
    }
};
