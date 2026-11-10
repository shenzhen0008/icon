<div class="space-y-4">
    @if ($positions->isEmpty())
        <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-10 w-10 text-gray-400 dark:text-gray-500 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
            暂无产品购买记录
        </div>
    @else
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <tr>
                        <th scope="col" class="px-4 py-3">订单号</th>
                        <th scope="col" class="px-4 py-3">产品名称</th>
                        <th scope="col" class="px-4 py-3 text-right">购买本金</th>
                        <th scope="col" class="px-4 py-3 text-center">状态</th>
                        <th scope="col" class="px-4 py-3">购买时间</th>
                        <th scope="col" class="px-4 py-3">结清时间</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($positions as $position)
                        <tr class="bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-mono font-medium text-gray-900 dark:text-white">
                                {{ $position->order_no ?? ('#' . $position->id) }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $position->product?->name ?? '--' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white">
                                {{ number_format((float) $position->principal, 2) }} <span class="text-xs text-gray-500">USDT</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($position->status === 'open')
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">
                                        持有中
                                    </span>
                                @elseif ($position->status === 'redeeming')
                                    <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20">
                                        赎回中
                                    </span>
                                @elseif ($position->status === 'closed')
                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20">
                                        已结清
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20">
                                        {{ $position->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                {{ $position->opened_at?->format('Y-m-d H:i:s') ?? '--' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                {{ $position->closed_at?->format('Y-m-d H:i:s') ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
