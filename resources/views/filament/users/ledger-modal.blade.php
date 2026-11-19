<div tabindex="0" role="region" aria-label="用户账单" class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
    @if ($ledgers->isEmpty())
        <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">暂无账单记录</div>
    @else
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                <tr>
                    <th scope="col" class="whitespace-nowrap px-3 py-2">时间</th>
                    <th scope="col" class="whitespace-nowrap px-3 py-2">类型</th>
                    <th scope="col" class="whitespace-nowrap px-3 py-2 text-right">金额 (USDT)</th>
                    <th scope="col" class="whitespace-nowrap px-3 py-2 text-right">变动前</th>
                    <th scope="col" class="whitespace-nowrap px-3 py-2 text-right">变动后</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach ($ledgers as $ledger)
                    <tr class="bg-white dark:bg-gray-900">
                        <td class="whitespace-nowrap px-3 py-2 text-xs text-gray-600 dark:text-gray-400">{{ $ledger->occurred_at?->copy()->setTimezone(config('settlement.timezone', 'Asia/Shanghai'))->format('Y-m-d H:i:s') ?? '--' }}</td>
                        <td class="whitespace-nowrap px-3 py-2">{{ match ($ledger->type) {
                            'recharge_credit' => '充值',
                            'purchase_debit' => '购买产品',
                            'settlement_credit' => '产品收益',
                            'principal_return_credit' => '本金返还',
                            'savings_interest_credit' => '储蓄收益',
                            'referral_commission_credit' => '推荐奖励',
                            'withdrawal_debit' => '提现',
                            'withdrawal_refund' => '提现退回',
                            'redemption_credit' => '赎回',
                            \App\Modules\Balance\Models\AdminReservationFundOrder::DEBIT => '预约资金扣款',
                            \App\Modules\Balance\Models\AdminReservationFundOrder::RELEASE => '预约资金解冻',
                            default => $ledger->type,
                        } }}
                            @if ($ledger->biz_ref_type === \App\Modules\Balance\Models\AdminReservationFundOrder::REFERENCE_TYPE)
                                <span class="block whitespace-normal break-all">{{ $ledger->reservationFundOrder?->name }}</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-right font-mono {{ (float) $ledger->amount >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">@if ($ledger->biz_ref_type === \App\Modules\Balance\Models\AdminReservationFundOrder::REFERENCE_TYPE)
                            {{ str_starts_with($ledger->amount, '-') ? '' : '+' }}{{ $ledger->amount }}
                        @else
                            {{ (float) $ledger->amount > 0 ? '+' : '' }}{{ number_format((float) $ledger->amount, 2) }}
                        @endif</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right font-mono">{{ number_format((float) $ledger->before_balance, 2) }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right font-mono">{{ number_format((float) $ledger->after_balance, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($ledgers->hasPages())
            <nav class="flex items-center justify-between border-t border-gray-200 px-3 py-2 text-sm dark:border-gray-800" aria-label="账单分页">
                <span class="text-xs text-gray-500">共 {{ $ledgers->total() }} 条</span>
                <div class="flex items-center gap-1">
                    @if ($ledgers->onFirstPage())
                        <span class="rounded border px-2 py-1 text-gray-400">上一页</span>
                    @else
                        <button type="button" wire:click="gotoPage({{ $ledgers->currentPage() - 1 }}, 'ledgerPage')" class="rounded border px-2 py-1">上一页</button>
                    @endif
                    <span class="px-2">{{ $ledgers->currentPage() }} / {{ $ledgers->lastPage() }}</span>
                    @if ($ledgers->hasMorePages())
                        <button type="button" wire:click="gotoPage({{ $ledgers->currentPage() + 1 }}, 'ledgerPage')" class="rounded border px-2 py-1">下一页</button>
                    @else
                        <span class="rounded border px-2 py-1 text-gray-400">下一页</span>
                    @endif
                </div>
            </nav>
        @endif
    @endif
</div>
