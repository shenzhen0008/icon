@props([
    'metrics' => [],
    'sharedProfit' => [],
])

<section
    class="mt-8 rounded-2xl border border-theme bg-theme-card p-5"
    data-shared-profit-base-value="{{ $sharedProfit['base_value'] ?? '0.00' }}"
    data-shared-profit-step-seconds="{{ $sharedProfit['step_seconds'] ?? 3 }}"
    data-shared-profit-min-delta="{{ $sharedProfit['min_delta'] ?? '0.00' }}"
    data-shared-profit-max-delta="{{ $sharedProfit['max_delta'] ?? '0.00' }}"
>
    <div class="mb-4 flex items-center justify-between gap-3">
        <h2 class="text-scale-title font-semibold text-theme">{{ __('pages/home.exchange.title') }}</h2>
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
                        <span class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-theme-secondary text-scale-micro font-semibold text-theme-secondary">
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

                <div class="hidden border-t border-theme px-4 py-3 text-scale-body" data-detail-row="{{ $metric['exchange_code'] }}">
                    <div class="grid grid-cols-3 border-b border-theme pb-2 text-scale-micro text-theme-secondary">
                        <span>{{ __('pages/home.exchange.currency') }}</span>
                        <span class="text-center">{{ __('pages/home.exchange.volume_24h') }}</span>
                        <span class="text-right">{{ __('pages/home.exchange.liquidity') }}</span>
                    </div>

                    <div class="mt-2 grid grid-cols-3 items-center gap-2">
                        <span class="flex items-center gap-2 text-theme-secondary">
                            <span class="text-scale-micro flex h-6 w-6 items-center justify-center overflow-hidden rounded-full bg-theme-secondary/80 font-semibold text-theme">
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
                            <span class="text-scale-micro flex h-6 w-6 items-center justify-center overflow-hidden rounded-full bg-theme-secondary/80 font-semibold text-theme">
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
                    <p class="mt-2 text-scale-micro text-theme-secondary" data-field="updated_at">{{ __('pages/home.exchange.updated_prefix') }}: --</p>
                </div>
            </article>
        @endforeach
    </div>
</section>

<script>
    (() => {
        const list = document.getElementById('exchange-metrics-list');
        if (!list) return;
        const section = list.closest('section[data-shared-profit-base-value]');
        const updatedFields = Array.from(list.querySelectorAll('[data-field="updated_at"]'));
        const profitFields = Array.from(list.querySelectorAll('[data-field="profit_value"]'));

        const refreshUpdatedAt = () => {
            const now = new Date();
            const timestamp = now.toLocaleString('sv-SE', { hour12: false }).replace('T', ' ');
            updatedFields.forEach((field) => {
                field.textContent = `${@json(__('pages/home.exchange.updated_prefix'))}: ${timestamp}`;
            });
        };

        const startProfitTicker = () => {
            if (!section || profitFields.length === 0 || typeof window.startBaseAnchoredTicker !== 'function') {
                return;
            }

            window.startBaseAnchoredTicker({
                elements: profitFields,
                baseValue: section.dataset.sharedProfitBaseValue,
                minDelta: section.dataset.sharedProfitMinDelta,
                maxDelta: section.dataset.sharedProfitMaxDelta,
                stepSeconds: Number(section.dataset.sharedProfitStepSeconds || 3),
                precision: 2,
            });
        };

        const ensureProfitTicker = () => {
            if (typeof window.startBaseAnchoredTicker === 'function') {
                startProfitTicker();
                return;
            }

            window.addEventListener('base-anchored-ticker:ready', startProfitTicker, { once: true });
        };

        list.querySelectorAll('[data-toggle-row]').forEach((button) => {
            const code = button.dataset.code;
            if (!code) return;

            const detail = list.querySelector(`[data-detail-row="${code}"]`);
            button.addEventListener('click', () => detail?.classList.toggle('hidden'));
        });

        refreshUpdatedAt();
        ensureProfitTicker();
        setInterval(refreshUpdatedAt, 1000);
    })();
</script>
