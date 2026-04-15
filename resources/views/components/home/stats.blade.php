<section class="mb-8 rounded-2xl border border-theme bg-theme-card p-5">
    <h2 class="text-scale-display font-semibold text-theme">Open transaction!</h2>
    <p class="mt-2 text-scale-body text-theme-secondary">2000+ base factor library with AI support to more catch derivative factors, one step ahead!</p>

    <div class="mt-5 rounded-xl border border-theme bg-theme-secondary/20 p-4">
        <div class="flex items-center justify-between border-b border-theme pb-3">
            <p class="text-scale-body text-theme-secondary">Number of people</p>
            <p class="text-scale-display font-semibold text-[rgb(var(--theme-primary))]" id="summary-participant-count">{{ $summary['participant_count'] }}</p>
        </div>
        <div class="mt-3 flex items-center justify-between">
            <p class="text-scale-body text-theme-secondary">总盘获利值</p>
            <p class="text-scale-display font-semibold text-[rgb(var(--theme-accent))]" id="summary-total-profit">{{ $summary['total_profit'] }} USDT</p>
        </div>
    </div>
</section>

<script>
    (() => {
        const participant = document.getElementById('summary-participant-count');
        const totalProfit = document.getElementById('summary-total-profit');

        if (!participant || !totalProfit) return;

        const refreshSummary = async () => {
            try {
                const response = await fetch('/home-summary', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) return;

                const payload = await response.json();

                if (typeof payload?.participant_count === 'string') {
                    participant.textContent = payload.participant_count;
                }

                if (typeof payload?.total_profit === 'string') {
                    totalProfit.textContent = `${payload.total_profit} USDT`;
                }
            } catch (_) {
                // Keep silent in MVP, next interval will retry.
            }
        };

        setInterval(refreshSummary, 3000);
    })();
</script>
