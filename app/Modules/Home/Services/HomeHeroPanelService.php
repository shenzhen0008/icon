<?php

namespace App\Modules\Home\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Services\ProductTranslationService;
use App\Modules\Settlement\Models\DailySettlement;
use App\Modules\Withdrawal\Models\WithdrawalRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class HomeHeroPanelService
{
    public function __construct(private readonly ProductTranslationService $productTranslationService)
    {
    }

    private const TRADE_LEDGER_TYPES = [
        'purchase_debit',
        'principal_return_credit',
        'withdrawal_debit',
        'withdrawal_refund',
    ];

    /**
     * @return array{
     *     mode:string,
     *     badge:string,
     *     available_balance:string,
     *     total_earnings:string,
     *     earnings_24h:string,
     *     trade_records:array<int, array<string, string>>,
     *     income_records:array<int, array<string, string>>
     * }
     */
    public function resolve(string $mode, ?int $userId = null): array
    {
        return $mode === 'live'
            ? $this->resolveLive($userId)
            : $this->resolveDemo();
    }

    /**
     * @return array{
     *     mode:string,
     *     badge:string,
     *     available_balance:string,
     *     total_earnings:string,
     *     earnings_24h:string,
     *     trade_records:array<int, array<string, string>>,
     *     income_records:array<int, array<string, string>>
     * }
     */
    private function resolveDemo(): array
    {
        $now = now();

        return [
            'mode' => 'demo',
            'badge' => '#demo',
            'available_balance' => '8888.88',
            'total_earnings' => '1288.80',
            'earnings_24h' => '88.80',
            'trade_records' => [
                [
                    'event_type' => 'purchase_debit',
                    'title' => 'BTC Grid Alpha',
                    'amount' => '1200.00',
                    'status' => 'completed',
                    'occurred_at' => $now->copy()->subHours(2)->format('Y-m-d H:i:s'),
                ],
                [
                    'event_type' => 'purchase_debit',
                    'title' => 'ETH Trend Pulse',
                    'amount' => '800.00',
                    'status' => 'completed',
                    'occurred_at' => $now->copy()->subDay()->format('Y-m-d H:i:s'),
                ],
            ],
            'income_records' => [
                [
                    'product_name' => 'BTC Grid Alpha',
                    'profit' => '58.80',
                    'rate_percent' => '2.10%',
                    'settlement_at' => $now->copy()->subHours(1)->format('Y-m-d H:i:s'),
                ],
                [
                    'product_name' => 'ETH Trend Pulse',
                    'profit' => '30.00',
                    'rate_percent' => '1.88%',
                    'settlement_at' => $now->copy()->subHours(20)->format('Y-m-d H:i:s'),
                ],
            ],
        ];
    }

    /**
     * @return array{
     *     mode:string,
     *     badge:string,
     *     available_balance:string,
     *     total_earnings:string,
     *     earnings_24h:string,
     *     trade_records:array<int, array<string, string>>,
     *     income_records:array<int, array<string, string>>
     * }
     */
    private function resolveLive(?int $userId): array
    {
        $user = User::query()->findOrFail($userId);
        $windowStart = now()->subDay();

        $totalEarnings = (float) DailySettlement::query()
            ->where('user_id', $user->id)
            ->sum('profit');
        $totalSavingsEarnings = (float) BalanceLedger::query()
            ->where('user_id', $user->id)
            ->where('type', 'savings_interest_credit')
            ->where('biz_ref_type', 'savings_interest')
            ->sum('amount');

        $earnings24h = (float) DailySettlement::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $windowStart)
            ->sum('profit');
        $savingsEarnings24h = (float) BalanceLedger::query()
            ->where('user_id', $user->id)
            ->where('type', 'savings_interest_credit')
            ->where('biz_ref_type', 'savings_interest')
            ->where('occurred_at', '>=', $windowStart)
            ->sum('amount');

        $tradeRecords = $this->tradeRecordQuery($user->id)
            ->limit(20)
            ->get()
            ->pipe(fn (Collection $ledgers): array => $this->mapTradeRecords($ledgers));

        $incomeRows = $this->incomeRecordQuery($user->id)
            ->limit(50)
            ->get();

        $incomeRecords = $this->mapIncomeRecords($incomeRows);

        return [
            'mode' => 'live',
            'badge' => '#live',
            'available_balance' => $this->formatMoney((float) $user->balance),
            'total_earnings' => $this->formatMoney($totalEarnings + $totalSavingsEarnings),
            'earnings_24h' => $this->formatMoney($earnings24h + $savingsEarnings24h),
            'trade_records' => $tradeRecords,
            'income_records' => $incomeRecords,
        ];
    }

    public function incomeRecordQuery(int $userId): QueryBuilder
    {
        $savingsRate = DB::table('savings_yield_settings')
            ->where('id', 1)
            ->value('daily_rate');

        $settlementQuery = DailySettlement::query()
            ->leftJoin('products', 'products.id', '=', 'daily_settlements.product_id')
            ->where('daily_settlements.user_id', $userId)
            ->selectRaw(
                "'settlement' as income_type, ".
                "daily_settlements.id as sort_id, ".
                "daily_settlements.product_id as product_id, ".
                "COALESCE(products.name, '--') as product_name, ".
                "daily_settlements.profit as profit, ".
                "daily_settlements.rate as rate, ".
                "daily_settlements.settlement_date as settlement_date, ".
                "daily_settlements.created_at as occurred_at"
            );

        $commissionQuery = BalanceLedger::query()
            ->where('balance_ledgers.user_id', $userId)
            ->where('balance_ledgers.type', 'referral_commission_credit')
            ->where('balance_ledgers.biz_ref_type', 'referral_commission')
            ->selectRaw(
                "'referral_commission' as income_type, ".
                "balance_ledgers.id as sort_id, ".
                "NULL as product_id, ".
                "'推荐提成' as product_name, ".
                "balance_ledgers.amount as profit, ".
                "NULL as rate, ".
                "balance_ledgers.settlement_date as settlement_date, ".
                "balance_ledgers.occurred_at as occurred_at"
            );

        $savingsQuery = BalanceLedger::query()
            ->where('balance_ledgers.user_id', $userId)
            ->where('balance_ledgers.type', 'savings_interest_credit')
            ->where('balance_ledgers.biz_ref_type', 'savings_interest')
            ->selectRaw(
                "'savings_interest' as income_type, ".
                "balance_ledgers.id as sort_id, ".
                "NULL as product_id, ".
                "'储蓄收益' as product_name, ".
                "balance_ledgers.amount as profit, ".
                "? as rate, ".
                "balance_ledgers.settlement_date as settlement_date, ".
                "balance_ledgers.occurred_at as occurred_at",
                [$savingsRate]
            );

        return DB::query()
            ->fromSub($settlementQuery->unionAll($commissionQuery)->unionAll($savingsQuery), 'income_records')
            ->orderByDesc('occurred_at')
            ->orderByDesc('sort_id');
    }

    private function formatIncomeOccurredAt(mixed $occurredAt): string
    {
        $timezone = (string) config('settlement.timezone', 'Asia/Shanghai');

        if ($occurredAt instanceof Carbon) {
            return $occurredAt->clone()->setTimezone($timezone)->format('Y-m-d H:i:s');
        }

        if (is_string($occurredAt) && trim($occurredAt) !== '') {
            try {
                return Carbon::parse($occurredAt, 'UTC')
                    ->setTimezone($timezone)
                    ->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                return $occurredAt;
            }
        }

        return '--';
    }

    private function formatSettlementAt(mixed $settlementDate, mixed $occurredAt): string
    {
        if ($settlementDate instanceof Carbon) {
            return $settlementDate->format('Y-m-d').' '.(string) config('settlement.run_at', '00:05').':00';
        }

        if (is_string($settlementDate) && trim($settlementDate) !== '') {
            return $settlementDate.' '.(string) config('settlement.run_at', '00:05').':00';
        }

        return $this->formatIncomeOccurredAt($occurredAt);
    }

    private function formatMoney(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    /**
     * @param iterable<int, stdClass> $incomeRows
     * @return array<int, array{income_type:string, product_name:string, profit:string, rate_percent:string, settlement_at:string}>
     */
    public function mapIncomeRecords(iterable $incomeRows): array
    {
        $incomeRowCollection = collect($incomeRows);
        $incomeProductNamesByRecordId = $this->resolveIncomeProductNames($incomeRowCollection);

        return $incomeRowCollection
            ->map(fn (stdClass $record): array => [
                'income_type' => (string) ($record->income_type ?? ''),
                'product_name' => $incomeProductNamesByRecordId[(int) ($record->sort_id ?? 0)] ?? (string) ($record->product_name ?? '--'),
                'profit' => $this->formatMoney((float) ($record->profit ?? 0)),
                'rate_percent' => $record->rate === null
                    ? '--'
                    : number_format((float) $record->rate * 100, 2, '.', '').'%',
                'settlement_at' => $this->formatSettlementAt($record->settlement_date ?? null, $record->occurred_at ?? null),
            ])
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, stdClass> $incomeRows
     * @return array<int, string>
     */
    private function resolveIncomeProductNames(Collection $incomeRows): array
    {
        $productIds = $incomeRows
            ->where('income_type', 'settlement')
            ->pluck('product_id')
            ->filter(static fn (mixed $id): bool => is_numeric($id) && (int) $id > 0)
            ->map(static fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return [];
        }

        $products = Product::query()
            ->with('translations')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy(fn (Product $product): int => (int) $product->id);

        $resolvedNames = [];

        foreach ($incomeRows as $row) {
            if (($row->income_type ?? '') !== 'settlement') {
                continue;
            }

            $productId = is_numeric($row->product_id ?? null) ? (int) $row->product_id : 0;
            if ($productId <= 0) {
                continue;
            }

            $product = $products->get($productId);
            if (! $product instanceof Product) {
                continue;
            }

            $resolvedNames[(int) ($row->sort_id ?? 0)] = $this->productTranslationService->resolveName($product, emptyFallback: '--');
        }

        return $resolvedNames;
    }

    public function tradeRecordQuery(int $userId): Builder
    {
        return BalanceLedger::query()
            ->where('user_id', $userId)
            ->whereIn('type', self::TRADE_LEDGER_TYPES)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');
    }

    /**
     * @param iterable<int, BalanceLedger> $ledgers
     * @return array<int, array<string, string>>
     */
    public function mapTradeRecords(iterable $ledgers): array
    {
        $ledgerCollection = collect($ledgers);
        if ($ledgerCollection->isEmpty()) {
            return [];
        }

        $positionIds = $ledgerCollection
            ->where('biz_ref_type', 'position')
            ->pluck('biz_ref_id')
            ->unique()
            ->values();

        $positions = Position::query()
            ->with(['product:id,name', 'product.translations'])
            ->whereIn('id', $positionIds)
            ->get()
            ->keyBy(fn (Position $position): string => (string) $position->id);

        $withdrawalRequestIds = $ledgerCollection
            ->where('biz_ref_type', 'withdrawal_request')
            ->pluck('biz_ref_id')
            ->unique()
            ->values();

        $withdrawalRequests = WithdrawalRequest::query()
            ->whereIn('id', $withdrawalRequestIds)
            ->get()
            ->keyBy(fn (WithdrawalRequest $request): string => (string) $request->id);

        return $ledgerCollection
            ->map(function (BalanceLedger $ledger) use ($positions, $withdrawalRequests): array {
                $occurredAt = $ledger->occurred_at instanceof Carbon
                    ? $ledger->occurred_at->format('Y-m-d H:i:s')
                    : '--';

                return match ($ledger->type) {
                    'purchase_debit' => [
                        'event_type' => 'purchase_debit',
                        'title' => $this->productTranslationService->resolveName($positions->get((string) $ledger->biz_ref_id)?->product, emptyFallback: '--'),
                        'amount' => $this->formatMoney(abs((float) $ledger->amount)),
                        'status' => 'completed',
                        'occurred_at' => $occurredAt,
                    ],
                    'principal_return_credit' => [
                        'event_type' => 'principal_return_credit',
                        'title' => $this->productTranslationService->resolveName($positions->get((string) $ledger->biz_ref_id)?->product, emptyFallback: '--'),
                        'amount' => $this->formatMoney(abs((float) $ledger->amount)),
                        'status' => 'completed',
                        'occurred_at' => $occurredAt,
                    ],
                    'withdrawal_debit' => [
                        'event_type' => 'withdrawal_debit',
                        'title' => '提款至 '.(string) ($withdrawalRequests->get((string) $ledger->biz_ref_id)?->destination_address ?? '--'),
                        'amount' => $this->formatMoney(abs((float) $ledger->amount)),
                        'status' => (string) ($withdrawalRequests->get((string) $ledger->biz_ref_id)?->status ?? 'pending'),
                        'occurred_at' => $occurredAt,
                    ],
                    'withdrawal_refund' => [
                        'event_type' => 'withdrawal_refund',
                        'title' => '提款驳回退款',
                        'amount' => $this->formatMoney(abs((float) $ledger->amount)),
                        'status' => 'refunded',
                        'occurred_at' => $occurredAt,
                    ],
                    default => [
                        'event_type' => (string) $ledger->type,
                        'title' => '--',
                        'amount' => $this->formatMoney(abs((float) $ledger->amount)),
                        'status' => '--',
                        'occurred_at' => $occurredAt,
                    ],
                };
            })
            ->values()
            ->all();
    }

}
