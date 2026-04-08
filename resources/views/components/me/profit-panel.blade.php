<section class="rounded-2xl border border-theme bg-theme-card p-5">
  <h2 class="text-base font-semibold text-theme">收益状态</h2>

  <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
    <div class="rounded-lg border border-[rgb(var(--theme-primary))]/25 bg-[rgb(var(--theme-primary))]/10 p-3">
      <p class="text-[rgb(var(--theme-primary))]">今日已结算收益</p>
      <p class="mt-1 text-lg font-semibold text-theme">{{ $summary['today_profit'] }}</p>
    </div>
    <div class="rounded-lg border border-[rgb(var(--theme-accent))]/25 bg-[rgb(var(--theme-accent))]/10 p-3">
      <p class="text-[rgb(var(--theme-accent))]">累计已结算收益</p>
      <p class="mt-1 text-lg font-semibold text-theme">{{ $summary['total_profit'] }}</p>
    </div>
    <div class="rounded-lg border border-theme bg-theme-secondary/20 p-3">
      <p class="text-theme-secondary">当前本金</p>
      <p class="mt-1 text-base font-semibold text-theme">{{ $summary['principal'] }}</p>
    </div>
    <div class="rounded-lg border border-theme bg-theme-secondary/20 p-3">
      <p class="text-theme-secondary">账户余额</p>
      <p class="mt-1 text-base font-semibold text-theme">{{ $summary['balance'] }}</p>
    </div>
  </div>
</section>
