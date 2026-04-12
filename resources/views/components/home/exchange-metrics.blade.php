<section class="mt-8 rounded-2xl border border-theme bg-theme-card p-5">
    <div class="mb-4 flex items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-theme">实时操盘平台</h2>
    </div>

    <div id="exchange-metrics-list" class="-mx-5 space-y-3">
        @foreach ($metrics as $metric)
            <article class="rounded-xl border border-theme bg-theme-secondary/20">
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left"
                    data-toggle-row
                    data-code="{{ $metric['exchange_code'] }}"
                >
                    <span class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-theme-secondary text-xs font-semibold text-theme-secondary">
                            <img
                                src="{{ $metric['logo_url'] }}"
                                alt="{{ $metric['exchange_name'] }} logo"
                                class="h-8 w-8 object-cover"
                                loading="lazy"
                                referrerpolicy="no-referrer"
                                onerror="this.classList.add('hidden'); this.parentElement.querySelector('[data-logo-fallback]').classList.remove('hidden');"
                            >
                            <span class="hidden" data-logo-fallback>{{ strtoupper(substr($metric['exchange_name'], 0, 1)) }}</span>
                        </span>
                        <span class="font-medium text-theme">{{ $metric['exchange_name'] }}</span>
                    </span>
                    <span class="font-semibold text-[rgb(var(--theme-primary))]" data-field="profit_value">{{ $metric['profit_value'] }}</span>
                </button>

                <div class="hidden border-t border-theme px-4 py-3 text-sm" data-detail-row="{{ $metric['exchange_code'] }}">
                    <div class="grid grid-cols-3 border-b border-theme pb-2 text-xs text-theme-secondary">
                        <span>Currency</span>
                        <span class="text-center">24h Volume</span>
                        <span class="text-right">Liquidity</span>
                    </div>

                    <div class="mt-2 grid grid-cols-3 items-center gap-2">
                        <span class="flex items-center gap-2 text-theme-secondary">
                            <span class="flex h-6 w-6 items-center justify-center overflow-hidden rounded-full bg-theme-secondary/80 text-[10px] font-semibold text-theme">
                                <img
                                    src="/images/assets/bitcoin.svg"
                                    alt="Bitcoin logo"
                                    class="h-4 w-4 object-contain"
                                    loading="lazy"
                                    onerror="this.classList.add('hidden'); this.parentElement.querySelector('[data-asset-fallback]').classList.remove('hidden');"
                                >
                                <span class="hidden" data-asset-fallback>B</span>
                            </span>
                            <span>BTC</span>
                        </span>
                        <span class="text-center text-theme" data-field="btc_value">{{ $metric['btc_value'] }}</span>
                        <span class="text-right text-theme" data-field="btc_liquidity">{{ $metric['btc_liquidity'] }}</span>
                    </div>
                    <div class="mt-2 grid grid-cols-3 items-center gap-2">
                        <span class="flex items-center gap-2 text-theme-secondary">
                            <span class="flex h-6 w-6 items-center justify-center overflow-hidden rounded-full bg-theme-secondary/80 text-[10px] font-semibold text-theme">
                                <img
                                    src="/images/assets/ethereum.svg"
                                    alt="Ethereum logo"
                                    class="h-4 w-4 object-contain"
                                    loading="lazy"
                                    onerror="this.classList.add('hidden'); this.parentElement.querySelector('[data-asset-fallback]').classList.remove('hidden');"
                                >
                                <span class="hidden" data-asset-fallback>E</span>
                            </span>
                            <span>ETH</span>
                        </span>
                        <span class="text-center text-theme" data-field="eth_value">{{ $metric['eth_value'] }}</span>
                        <span class="text-right text-theme" data-field="eth_liquidity">{{ $metric['eth_liquidity'] }}</span>
                    </div>
                    <p class="mt-2 text-xs text-theme-secondary" data-field="updated_at">更新: {{ $metric['updated_at'] }}</p>
                </div>
            </article>
        @endforeach
    </div>
</section>

<script>
    (() => {
        const list = document.getElementById('exchange-metrics-list');
        if (!list) return;
        const summaryParticipantCount = document.getElementById('summary-participant-count');
        const summaryTotalProfit = document.getElementById('summary-total-profit');

        const rowCache = new Map();

        const formatProfit = (value) => Number(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });

        const animateValue = (node, nextRaw, duration = 600) => {
            const start = Number((node.textContent || '0').replace(/,/g, '')) || 0;
            const end = Number(nextRaw) || 0;
            const delta = end - start;

            if (Math.abs(delta) < 0.0001) {
                node.textContent = formatProfit(end);
                return;
            }

            const startAt = performance.now();
            const tick = (time) => {
                const progress = Math.min(1, (time - startAt) / duration);
                const current = Math.round(start + (delta * progress));
                node.textContent = formatProfit(current);
                if (progress < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        };

        const bindRows = () => {
            list.querySelectorAll('[data-toggle-row]').forEach((button) => {
                const code = button.dataset.code;
                if (!code) return;

                const detail = list.querySelector(`[data-detail-row="${code}"]`);
                const profit = button.querySelector('[data-field="profit_value"]');
                const btc = detail?.querySelector('[data-field="btc_value"]');
                const btcLiquidity = detail?.querySelector('[data-field="btc_liquidity"]');
                const eth = detail?.querySelector('[data-field="eth_value"]');
                const ethLiquidity = detail?.querySelector('[data-field="eth_liquidity"]');
                const updated = detail?.querySelector('[data-field="updated_at"]');

                rowCache.set(code, { profit, btc, btcLiquidity, eth, ethLiquidity, updated });
                button.addEventListener('click', () => detail?.classList.toggle('hidden'));
            });
        };

        const refresh = async () => {
            try {
                const response = await fetch('/exchange-metrics', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) return;
                const payload = await response.json();
                const rows = payload?.data || [];
                let totalProfit = 0;
                let participantCount = 0;

                rows.forEach((row) => {
                    const refs = rowCache.get(row.exchange_code);
                    if (!refs) return;
                    totalProfit += Number(row.profit_value || 0);
                    participantCount += Number(row.btc_liquidity || 0) + Number(row.eth_liquidity || 0);
                    if (refs.profit) animateValue(refs.profit, row.profit_value);
                    if (refs.btc) refs.btc.textContent = row.btc_value;
                    if (refs.btcLiquidity) refs.btcLiquidity.textContent = row.btc_liquidity;
                    if (refs.eth) refs.eth.textContent = row.eth_value;
                    if (refs.ethLiquidity) refs.ethLiquidity.textContent = row.eth_liquidity;
                    if (refs.updated) refs.updated.textContent = `更新: ${row.updated_at}`;
                });

                if (summaryParticipantCount) {
                    summaryParticipantCount.textContent = Number(participantCount || 0).toLocaleString('en-US');
                }
                if (summaryTotalProfit) {
                    summaryTotalProfit.textContent = `$${formatProfit(totalProfit)}`;
                }
            } catch (_) {
                // Keep silent in MVP, next interval will retry.
            }
        };

        bindRows();
        setInterval(refresh, 2000);
    })();
</script>
