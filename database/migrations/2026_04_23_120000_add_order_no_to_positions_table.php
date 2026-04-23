<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table): void {
            $table->string('order_no', 6)->nullable()->after('id');
            $table->unique('order_no');
        });

        $totalPositions = DB::table('positions')->count();
        if ($totalPositions > 900000) {
            throw new \RuntimeException('positions 数据量超过 6 位随机订单号容量上限。');
        }

        DB::table('positions')
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(200, function ($positions): void {
                foreach ($positions as $position) {
                    $orderNo = $this->buildUniqueOrderNo();
                    DB::table('positions')
                        ->where('id', (int) $position->id)
                        ->update([
                            'order_no' => $orderNo,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table): void {
            $table->dropUnique('positions_order_no_unique');
            $table->dropColumn('order_no');
        });
    }

    /**
     * @var array<string, true>
     */
    private array $usedOrderNos = [];

    private function buildUniqueOrderNo(): string
    {
        $maxAttempts = 100;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $orderNo = (string) random_int(100000, 999999);

            if (isset($this->usedOrderNos[$orderNo])) {
                continue;
            }

            $exists = DB::table('positions')
                ->where('order_no', $orderNo)
                ->exists();

            if (! $exists) {
                $this->usedOrderNos[$orderNo] = true;

                return $orderNo;
            }
        }

        throw new \RuntimeException('无法为历史持仓生成唯一的 6 位随机订单号。');
    }
};
