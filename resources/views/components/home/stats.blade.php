<section class="mb-8 rounded-2xl border border-theme bg-theme-card p-5">
    <h2 class="text-scale-display font-semibold text-theme">总盘数据</h2>
    <p class="mt-2 text-scale-body text-theme-secondary">基于下方所有交易所实时汇总。</p>

    <div class="mt-5 rounded-xl border border-theme bg-theme-secondary/20 p-4">
        <div class="flex items-center justify-between border-b border-theme pb-3">
            <p class="text-scale-body text-theme-secondary">Number of people</p>
            <p class="text-scale-display font-semibold text-[rgb(var(--theme-primary))]" id="summary-participant-count">{{ $summary['participant_count'] }}</p>
        </div>
        <div class="mt-3 flex items-center justify-between">
            <p class="text-scale-body text-theme-secondary">总盘获利值</p>
            <p class="text-scale-display font-semibold text-[rgb(var(--theme-accent))]" id="summary-total-profit">{{ $summary['total_profit'] }}</p>
        </div>
    </div>
</section>
