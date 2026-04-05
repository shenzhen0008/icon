<section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5">
  <h2 class="text-base font-semibold">收益状态</h2>

  <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
    <div class="rounded-lg border border-emerald-500/25 bg-emerald-500/10 p-3">
      <p class="text-emerald-200">今日已结算收益</p>
      <p class="mt-1 text-lg font-semibold text-emerald-100">{{ $summary['today_profit'] }}</p>
    </div>
    <div class="rounded-lg border border-cyan-500/25 bg-cyan-500/10 p-3">
      <p class="text-cyan-200">累计已结算收益</p>
      <p class="mt-1 text-lg font-semibold text-cyan-100">{{ $summary['total_profit'] }}</p>
    </div>
    <div class="rounded-lg border border-white/10 bg-slate-950/40 p-3">
      <p class="text-slate-400">当前本金</p>
      <p class="mt-1 text-base font-semibold text-slate-100">{{ $summary['principal'] }}</p>
    </div>
    <div class="rounded-lg border border-white/10 bg-slate-950/40 p-3">
      <p class="text-slate-400">账户余额</p>
      <p class="mt-1 text-base font-semibold text-slate-100">{{ $summary['balance'] }}</p>
    </div>
  </div>
</section>
