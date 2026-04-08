<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recharge_receivers', function (Blueprint $table): void {
            $table->id();
            $table->string('asset_code', 20);
            $table->string('asset_name', 50);
            $table->string('network', 50);
            $table->string('address', 255);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->unique('asset_code');
            $table->index(['is_active', 'sort']);
        });

        $assets = (array) config('recharge.assets', []);
        $rows = [];
        $now = now();

        foreach ($assets as $asset) {
            if (! is_array($asset)) {
                continue;
            }

            $code = isset($asset['code']) ? (string) $asset['code'] : '';
            $network = isset($asset['network']) ? (string) $asset['network'] : '';
            $address = isset($asset['address']) ? (string) $asset['address'] : '';

            if ($code === '' || $address === '') {
                continue;
            }

            $rows[] = [
                'asset_code' => $code,
                'asset_name' => isset($asset['name']) ? (string) $asset['name'] : $code,
                'network' => $network,
                'address' => $address,
                'is_active' => true,
                'sort' => count($rows),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('recharge_receivers')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recharge_receivers');
    }
};
