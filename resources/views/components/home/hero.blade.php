@props([
    'paymentConfig' => [],
])

<section id="home-data-panel" class="mb-8 overflow-hidden rounded-2xl border border-theme bg-theme-card p-5 shadow-xl shadow-theme">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-scale-display font-semibold text-theme">Welcome to AI Smart Contracts</h1>
            <p class="mt-2 text-scale-body text-theme-secondary">Artificial intelligence trading</p>
        </div>
        <span id="hero-mode-badge" class="inline-flex w-20 justify-center rounded-full border border-theme bg-theme-secondary/30 px-3 py-1 text-scale-body text-theme">demo</span>
    </div>

    <div class="mt-5 rounded-xl border border-theme bg-theme-secondary/60 p-4">
        <div class="grid grid-cols-2 gap-3">
            <div class="min-w-0 pr-3">
                <p class="text-scale-body text-theme-secondary whitespace-nowrap">Total earnings (USDT)</p>
                <p id="hero-total-earnings" class="mt-2 h-8 overflow-hidden text-ellipsis whitespace-nowrap font-mono text-scale-title font-semibold leading-none tabular-nums text-theme sm:h-9 text-scale-display">$0.00</p>
            </div>
            <div class="min-w-0 border-l border-theme pl-3">
                <p class="text-scale-body text-theme-secondary whitespace-nowrap">Earnings 24h (USDT)</p>
                <p id="hero-earnings-24h" class="mt-2 h-8 overflow-hidden text-ellipsis whitespace-nowrap font-mono text-scale-title font-semibold leading-none tabular-nums text-theme sm:h-9 text-scale-display">$0.00</p>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <a
            id="home-onchain-entry"
            href="/recharge/onchain"
            data-token-address="{{ $paymentConfig['token_address'] ?? '' }}"
            data-to-address="{{ $paymentConfig['to_address'] ?? '' }}"
            data-payment-amount="{{ $paymentConfig['amount'] ?? '10' }}"
            data-asset-code="{{ $paymentConfig['asset_code'] ?? '' }}"
            class="inline-flex items-center justify-center rounded-xl bg-[rgb(var(--theme-primary))] px-4 py-3 text-scale-body font-semibold text-theme-on-primary transition hover:bg-[rgb(var(--theme-primary))]/90"
        >直接付款（链上充值）</a>
    </div>

    <div class="mt-5 grid grid-cols-2 gap-3">
        <button id="hero-damo-btn" type="button" class="inline-flex items-center justify-center rounded-xl bg-theme-secondary px-4 py-3 text-scale-body font-semibold text-theme transition hover:bg-theme-secondary/80">
            DEMO
        </button>
        <button id="hero-live-btn" type="button" class="inline-flex items-center justify-center rounded-xl bg-theme-secondary px-4 py-3 text-scale-body font-semibold text-theme transition hover:bg-theme-secondary/80">
            LIVE
        </button>
    </div>
</section>

<script>
    (() => {
        const panel = document.getElementById('home-data-panel');
        if (!panel) return;

        const modeBadge = document.getElementById('hero-mode-badge');
        const totalEarnings = document.getElementById('hero-total-earnings');
        const earnings24h = document.getElementById('hero-earnings-24h');
        const damoBtn = document.getElementById('hero-damo-btn');
        const liveBtn = document.getElementById('hero-live-btn');

        const formatMoney = (value) => `$${Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        const dataset = {
            damo: { total: 0, day: 0, badge: '#demo' },
            live: { total: 100.25, day: 50.25, badge: '#live' },
        };

        const setMode = (mode) => {
            const selected = dataset[mode];
            if (!selected) return;

            if (modeBadge) modeBadge.textContent = selected.badge;
            if (totalEarnings) totalEarnings.textContent = formatMoney(selected.total);
            if (earnings24h) earnings24h.textContent = formatMoney(selected.day);

            const damoActive = mode === 'damo';
            const setButtonActiveState = (button, active) => {
                button?.classList.toggle('bg-gradient-to-r', active);
                button?.classList.toggle('from-cyan-500', active);
                button?.classList.toggle('to-blue-500', active);
                button?.classList.toggle('text-slate-950', active);
                button?.classList.toggle('hover:from-cyan-400', active);
                button?.classList.toggle('hover:to-blue-400', active);

                button?.classList.toggle('bg-slate-700', !active);
                button?.classList.toggle('text-slate-100', !active);
                button?.classList.toggle('hover:bg-slate-600', !active);
            };

            setButtonActiveState(damoBtn, damoActive);
            setButtonActiveState(liveBtn, !damoActive);
        };

        damoBtn?.addEventListener('click', () => setMode('damo'));
        liveBtn?.addEventListener('click', () => setMode('live'));

        setMode('damo');
    })();
</script>
