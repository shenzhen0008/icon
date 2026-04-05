<section class="mb-8 rounded-2xl border border-white/10 bg-slate-900/60 p-5">
    <h2 class="text-2xl font-semibold text-white">总盘数据</h2>
    <p class="mt-2 text-sm text-slate-400">基于下方所有交易所实时汇总。</p>

    <div class="mt-5 rounded-xl border border-white/10 bg-slate-950/50 p-4">
        <div class="flex items-center justify-between border-b border-white/10 pb-3">
            <p class="text-sm text-slate-400">Number of people</p>
            <p class="text-2xl font-semibold text-cyan-300" id="summary-participant-count">{{ $summary['participant_count'] }}</p>
        </div>
        <div class="mt-3 flex items-center justify-between">
            <p class="text-sm text-slate-400">总盘获利值</p>
            <p class="text-2xl font-semibold text-emerald-300" id="summary-total-profit">{{ $summary['total_profit'] }}</p>
        </div>
    </div>
</section>
