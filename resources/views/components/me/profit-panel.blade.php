<x-ui.metric-split-card :use-split-layout="false" wrapper-class="home-data-panel">
  <h2 class="text-scale-body font-semibold text-theme">收益状态</h2>

  <div class="mt-4 grid grid-cols-2 gap-3 text-scale-body">
    <div class="rounded-lg border border-[rgb(var(--theme-primary))]/25 bg-[rgb(var(--theme-primary))]/10 p-3">
      <p class="text-[rgb(var(--theme-primary))]">今日已结算收益</p>
      <p class="mt-1 text-scale-title font-semibold text-theme">{{ $summary['today_profit'] }}</p>
    </div>
    <div class="rounded-lg border border-[rgb(var(--theme-accent))]/25 bg-[rgb(var(--theme-accent))]/10 p-3">
      <p class="text-[rgb(var(--theme-accent))]">累计已结算收益</p>
      <p class="mt-1 text-scale-title font-semibold text-theme">{{ $summary['total_profit'] }}</p>
    </div>
    <div class="rounded-lg border border-theme bg-theme-secondary/20 p-3">
      <p class="text-theme-secondary">当前本金</p>
      <p class="mt-1 text-scale-body font-semibold text-theme">{{ $summary['principal'] }}</p>
    </div>
    <div class="relative rounded-lg border border-theme bg-theme-secondary/20 p-3 pr-[6.5rem]">
      <div class="min-w-0">
        <p class="text-theme-secondary">账户余额</p>
        <p class="mt-1 text-scale-body font-semibold text-theme">{{ $summary['balance'] }}</p>
      </div>
      <a href="/recharge" class="text-scale-ui absolute bottom-3 right-3 inline-flex h-10 min-w-[5.25rem] items-center justify-center rounded-lg border border-[rgb(var(--theme-primary))]/35 bg-[rgb(var(--theme-primary))]/14 px-4 font-semibold text-[rgb(var(--theme-primary))] transition hover:border-[rgb(var(--theme-primary))] hover:bg-[rgb(var(--theme-primary))]/20">
        充值
      </a>
    </div>
  </div>
</x-ui.metric-split-card>
