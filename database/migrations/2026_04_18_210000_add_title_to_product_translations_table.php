<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_translations', 'title')) {
            Schema::table('product_translations', function (Blueprint $table): void {
                $table->string('title', 255)->nullable()->after('locale');
            });
        }

        $fallbackLocale = (string) config('i18n.fallback_locale', config('app.fallback_locale', 'zh-CN'));

        DB::table('product_translations')
            ->where('locale', $fallbackLocale)
            ->where(function ($query): void {
                $query->whereNull('title')->orWhere('title', '');
            })
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                $productNameMap = DB::table('products')
                    ->whereIn('id', collect($rows)->pluck('product_id')->all())
                    ->pluck('name', 'id');

                foreach ($rows as $row) {
                    $name = $productNameMap[$row->product_id] ?? null;
                    if (! is_string($name) || trim($name) === '') {
                        continue;
                    }

                    DB::table('product_translations')
                        ->where('id', $row->id)
                        ->update(['title' => $name]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_translations', 'title')) {
            Schema::table('product_translations', function (Blueprint $table): void {
                $table->dropColumn('title');
            });
        }
    }
};
