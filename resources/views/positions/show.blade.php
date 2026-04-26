<!doctype html>
<html lang="{{ __('pages/positions.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/positions.meta_title', ['app_name' => config('app.name')]) }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  @php
    $statusLabels = [
      'open' => __('pages/positions.status.open'),
      'redeeming' => __('pages/positions.status.redeeming'),
      'redeemed' => __('pages/positions.status.redeemed'),
    ];
  @endphp

  <main class="mx-auto w-full max-w-4xl px-4 pb-4 pt-6 md:pb-8 md:pt-8">
    <section class="overflow-hidden rounded-2xl border border-theme bg-theme-card p-2 text-theme shadow-xl shadow-[rgb(var(--theme-primary))]/10">
      <div class="flex items-start justify-between gap-3 overflow-x-auto whitespace-nowrap [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <div class="shrink-0 text-left">
          <p class="text-scale-micro text-theme-secondary">{{ __('pages/positions.labels.escrow_amount') }}</p>
          <p class="text-scale-ui mt-1 whitespace-nowrap font-medium text-theme">{{ $position['principal'] }}</p>
        </div>
        <div class="shrink-0 text-center">
          <p class="text-scale-micro text-theme-secondary">{{ __('pages/positions.labels.yield_rate') }}</p>
          <p class="text-scale-ui mt-1 whitespace-nowrap font-medium text-theme">{{ $position['rate_range'] }}</p>
        </div>
        <div class="shrink-0 text-right">
          <p class="text-scale-micro text-theme-secondary">{{ __('pages/positions.labels.cycle') }}</p>
          <p class="text-scale-ui mt-1 whitespace-nowrap font-medium text-theme">{{ $position['cycle_label'] }}</p>
        </div>
      </div>

      <h2 class="mt-3 text-scale-body font-semibold">{{ __('pages/positions.title') }}</h2>

      @if ($can_apply_redemption)
        <form method="POST" action="/me/positions/{{ $position['id'] }}/redemption-requests" class="mt-4" onsubmit="return confirm(@js(__('pages/positions.redemption_confirm')));">
          @csrf
          <button class="text-scale-ui mt-2.5 mx-auto flex h-[clamp(1.9rem,7vw,2.2rem)] w-[clamp(7rem,42vw,9rem)] items-center justify-center rounded-2xl bg-[rgb(var(--theme-primary))] px-[clamp(0.6rem,2.5vw,0.9rem)] font-semibold text-theme-on-primary shadow-lg shadow-[rgb(var(--theme-primary))]/20 transition hover:bg-[rgb(var(--theme-primary))]/90">{{ __('pages/positions.apply_redemption') }}</button>
        </form>
      @elseif ($redemption_request_status === 'pending')
        <p class="mt-4 text-scale-body text-amber-300">{{ __('pages/positions.redemption_pending') }}</p>
      @elseif ($position['status'] === 'redeemed')
        <p class="mt-4 text-scale-body text-emerald-300">{{ __('pages/positions.redeemed_done') }}</p>
      @endif

      @error('position')
        <p class="mt-3 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
      @enderror

      <dl class="mt-4 divide-y divide-theme overflow-hidden rounded-xl border border-theme bg-theme-secondary/40 text-scale-body">
        <div class="flex items-center justify-between gap-3 px-4 py-3">
          <dt class="text-theme-secondary">{{ __('pages/positions.labels.order_type') }}</dt>
          <dd class="flex items-center justify-end gap-2 text-right text-theme">
            <span class="flex h-[clamp(1.75rem,6vw,2.25rem)] w-[clamp(1.75rem,6vw,2.25rem)] shrink-0 items-center justify-center overflow-hidden rounded-full border border-theme bg-theme-secondary/80 text-theme">
              @if (!empty($position['product_icon_path']))
                <img src="{{ $position['product_icon_path'] }}" alt="" class="h-[clamp(1.125rem,4vw,1.5rem)] w-[clamp(1.125rem,4vw,1.5rem)] object-contain">
              @else
                <img src="{{ asset('images/icon_pro_usdt.svg') }}" alt="" class="h-full w-full object-contain">
              @endif
            </span>
            <span>{{ $position['product_name'] }}</span>
          </dd>
        </div>
        <div class="flex items-center justify-between gap-3 px-4 py-3">
          <dt class="text-theme-secondary">{{ __('pages/positions.labels.escrow_amount') }}</dt>
          <dd class="text-right text-theme">{{ $position['principal'] }}</dd>
        </div>
        <div class="flex items-center justify-between gap-3 px-4 py-3">
          <dt class="text-theme-secondary">{{ __('pages/positions.labels.yield_rate') }}</dt>
          <dd class="text-right text-theme">{{ $position['rate_range'] }}</dd>
        </div>
        <div class="flex items-center justify-between gap-3 px-4 py-3">
          <dt class="text-theme-secondary">{{ __('pages/positions.labels.profit') }}</dt>
          <dd class="text-right text-theme">{{ $position['total_profit'] }}</dd>
        </div>
        <div class="flex items-center justify-between gap-3 px-4 py-3">
          <dt class="text-theme-secondary">{{ __('pages/positions.labels.order_time') }}</dt>
          <dd class="text-right text-theme">{{ $position['opened_at'] }}</dd>
        </div>
        <div class="flex items-center justify-between gap-3 px-4 py-3">
          <dt class="text-theme-secondary">{{ __('pages/positions.labels.expire_date') }}</dt>
          <dd class="text-right text-theme">{{ $position['expire_at'] }}</dd>
        </div>
        <div class="flex items-center justify-between gap-3 px-4 py-3">
          <dt class="text-theme-secondary">{{ __('pages/positions.labels.order_id') }}</dt>
          <dd class="text-right text-theme">{{ $position['order_no'] }}</dd>
        </div>
        <div class="flex items-center justify-between gap-3 px-4 py-3">
          <dt class="text-theme-secondary">{{ __('pages/positions.labels.order_status') }}</dt>
          <dd class="text-right text-theme">{{ $statusLabels[$position['status']] ?? $position['status'] }}</dd>
        </div>
        <div class="px-4 py-3">
          <div class="mt-2.5 rounded-2xl border border-theme bg-theme-secondary/20 px-2 py-[0.1rem]">
            <div class="flex flex-nowrap items-center gap-2 overflow-x-auto overflow-y-hidden whitespace-nowrap pb-[0.05rem] [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
              @foreach ($position['symbol_icon_paths'] as $iconPath)
                <span class="flex h-[clamp(1.95rem,6.8vw,2.45rem)] w-[clamp(1.95rem,6.8vw,2.45rem)] shrink-0 items-center justify-center overflow-hidden rounded-full border border-theme bg-theme-card">
                  <img src="{{ $iconPath }}" alt="" class="h-[clamp(1.45rem,5.2vw,1.82rem)] w-[clamp(1.45rem,5.2vw,1.82rem)] object-contain">
                </span>
              @endforeach
            </div>
          </div>
        </div>
      </dl>
    </section>

    <section class="mt-6 rounded-2xl border border-theme bg-theme-card p-5">
      <h2 class="text-scale-body font-semibold">{{ __('pages/positions.daily_profit_title') }}</h2>

      @if (count($daily_profits) === 0)
        <div class="mt-4 rounded-xl border border-dashed border-theme bg-theme-secondary/40 p-4 text-scale-body text-theme-secondary">
          {{ __('pages/positions.daily_profit_empty') }}
        </div>
      @else
        <div class="mt-4 overflow-hidden rounded-xl border border-theme">
          <table class="min-w-full text-scale-body">
            <thead class="bg-theme-secondary/80 text-theme-secondary">
              <tr>
                <th class="px-4 py-3 text-left font-medium">{{ __('pages/positions.columns.settlement_date') }}</th>
                <th class="px-4 py-3 text-right font-medium">{{ __('pages/positions.columns.daily_rate') }}</th>
                <th class="px-4 py-3 text-right font-medium">{{ __('pages/positions.columns.daily_profit') }}</th>
              </tr>
            </thead>
            <tbody class="bg-theme-secondary/40">
              @foreach ($daily_profits as $row)
                <tr class="border-t border-theme">
                  <td class="px-4 py-3 text-theme">{{ $row['date'] }}</td>
                  <td class="px-4 py-3 text-right text-theme-secondary">{{ $row['rate_percent'] }}</td>
                  <td class="px-4 py-3 text-right text-emerald-300">{{ $row['profit'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </section>
  </main>

  <x-nav.mobile />
</body>
</html>
