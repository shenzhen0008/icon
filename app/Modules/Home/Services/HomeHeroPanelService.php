<?php

namespace App\Modules\Home\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Position\Models\Position;
use App\Modules\Settlement\Models\DailySettlement;
use App\Modules\Withdrawal\Models\WithdrawalRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class HomeHeroPanelService
{
    private const TRADE_LEDGER_TYPES = [
        'purchase_debit',
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

        $earnings24h = (float) DailySettlement::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $windowStart)
            ->sum('profit');

        $tradeRecords = $this->tradeRecordQuery($user->id)
            ->limit(20)
            ->get()
            ->pipe(fn (Collection $ledgers): array => $this->mapTradeRecords($ledgers));

        $incomeRecords = DailySettlement::query()
            ->with('product:id,name')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn (DailySettlement $settlement): array => [
                'product_name' => (string) ($settlement->product?->name ?? '--'),
                'profit' => $this->formatMoney((float) $settlement->profit),
                'settlement_at' => $this->formatSettlementTime($settlement),
            ])
            ->values()
            ->all();

        return [
            'mode' => 'live',
            'badge' => '#live',
            'available_balance' => $this->formatMoney((float) $user->balance),
            'total_earnings' => $this->formatMoney($totalEarnings),
            'earnings_24h' => $this->formatMoney($earnings24h),
            'trade_records' => $tradeRecords,
            'income_records' => $incomeRecords,
        ];
    }

    private function formatMoney(float $value): string
    {
        return number_format($value, 2, '.', '');
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
            ->with('product:id,name')
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
                        'title' => (string) ($positions->get((string) $ledger->biz_ref_id)?->product?->name ?? '--'),
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

    private function formatSettlementTime(DailySettlement $settlement): string
    {
        $createdAt = $settlement->created_at;
        if ($createdAt instanceof Carbon) {
            return $createdAt->format('Y-m-d H:i:s');
        }

        return optional($settlement->settlement_date)->format('Y-m-d').' 00:00:00';
    }
}
