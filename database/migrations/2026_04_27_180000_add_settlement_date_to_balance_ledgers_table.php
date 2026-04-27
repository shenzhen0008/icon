<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('balance_ledgers', function (Blueprint $table): void {
            $table->date('settlement_date')->nullable()->after('biz_ref_id');
            $table->index(['user_id', 'settlement_date'], 'balance_ledgers_user_id_settlement_date_index');
        });

        DB::statement(<<<'SQL'
UPDATE balance_ledgers
SET settlement_date = SUBSTRING_INDEX(biz_ref_id, ':', -1)
WHERE settlement_date IS NULL
  AND type = 'settlement_credit'
  AND biz_ref_type = 'daily_settlement'
  AND biz_ref_id LIKE '%:%'
SQL);

        DB::statement(<<<'SQL'
UPDATE balance_ledgers
SET settlement_date = SUBSTRING_INDEX(biz_ref_id, ':', 1)
WHERE settlement_date IS NULL
  AND type = 'savings_interest_credit'
  AND biz_ref_type = 'savings_interest'
  AND biz_ref_id LIKE '%:%'
SQL);

        DB::statement(<<<'SQL'
UPDATE balance_ledgers bl
INNER JOIN referral_commission_records rcr
    ON bl.biz_ref_id = CONCAT('settlement:', rcr.settlement_id, ':level:', rcr.level)
INNER JOIN daily_settlements ds
    ON ds.id = rcr.settlement_id
SET bl.settlement_date = ds.settlement_date
WHERE bl.settlement_date IS NULL
  AND bl.type = 'referral_commission_credit'
  AND bl.biz_ref_type = 'referral_commission'
SQL);
    }

    public function down(): void
    {
        Schema::table('balance_ledgers', function (Blueprint $table): void {
            $table->dropIndex('balance_ledgers_user_id_settlement_date_index');
            $table->dropColumn('settlement_date');
        });
    }
};
