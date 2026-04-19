@php
  $localeQuery = 'locale='.urlencode(app()->getLocale());
@endphp

<x-ui.metric-split-card :use-split-layout="false" wrapper-class="home-data-panel">
  <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/me.profit_panel.title') }}</h2>

  <div class="mt-4 grid grid-cols-2 gap-3 text-scale-body">
    <div class="rounded-lg border border-[rgb(var(--theme-primary))]/25 bg-[rgb(var(--theme-primary))]/10 p-3">
      <p class="text-[rgb(var(--theme-primary))]">{{ __('pages/me.profit_panel.today_settled') }}</p>
      <p class="mt-1 text-scale-title font-semibold text-theme">{{ $summary['today_profit'] }}</p>
    </div>
    <div class="rounded-lg border border-[rgb(var(--theme-accent))]/25 bg-[rgb(var(--theme-accent))]/10 p-3">
      <p class="text-[rgb(var(--theme-accent))]">{{ __('pages/me.profit_panel.total_settled') }}</p>
      <p class="mt-1 text-scale-title font-semibold text-theme">{{ $summary['total_profit'] }}</p>
    </div>
    <div class="rounded-lg border border-theme bg-theme-secondary/20 p-3">
      <p class="text-theme-secondary">{{ __('pages/me.profit_panel.current_principal') }}</p>
      <p class="mt-1 text-scale-body font-semibold text-theme">{{ $summary['principal'] }}</p>
    </div>
    <div class="relative rounded-lg border border-theme bg-theme-secondary/20 p-3 pr-[6.5rem]">
      <div class="min-w-0">
        <p class="text-theme-secondary">{{ __('pages/me.profit_panel.account_balance') }}</p>
        <p class="mt-1 text-scale-body font-semibold text-theme">{{ $summary['balance'] }}</p>
      </div>
      <a href="/recharge?{{ $localeQuery }}" class="text-scale-ui absolute bottom-3 right-3 inline-flex h-10 min-w-[5.25rem] items-center justify-center rounded-lg border border-[rgb(var(--theme-primary))]/35 bg-[rgb(var(--theme-primary))]/14 px-4 font-semibold text-[rgb(var(--theme-primary))] transition hover:border-[rgb(var(--theme-primary))] hover:bg-[rgb(var(--theme-primary))]/20">
        {{ __('pages/me.profit_panel.recharge') }}
      </a>
    </div>
  </div>
</x-ui.metric-split-card>
