<!doctype html>
<html lang="{{ __('pages/positions.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/positions.meta_title') }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  @php
    $statusLabels = [
      'open' => __('pages/positions.status.open'),
      'redeeming' => __('pages/positions.status.redeeming'),
      'redeemed' => __('pages/positions.status.redeemed'),
    ];
  @endphp

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-6 md:pb-10 md:pt-8">
    <section class="rounded-2xl border border-theme bg-theme-card p-5">
      <h1 class="text-scale-title font-semibold">{{ __('pages/positions.title') }}</h1>

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

      <dl class="mt-4 grid grid-cols-2 gap-3 text-scale-body">
        <div class="rounded-lg border border-theme bg-theme-secondary/40 p-3">
          <dt class="text-theme-secondary">{{ __('pages/positions.labels.order_id') }}</dt>
          <dd class="mt-1 text-theme">{{ $position['id'] }}</dd>
        </div>
        <div class="rounded-lg border border-theme bg-theme-secondary/40 p-3">
          <dt class="text-theme-secondary">{{ __('pages/positions.labels.product') }}</dt>
          <dd class="mt-1 text-theme">{{ $position['product_name'] }}</dd>
        </div>
        <div class="rounded-lg border border-theme bg-theme-secondary/40 p-3">
          <dt class="text-theme-secondary">{{ __('pages/positions.labels.principal') }}</dt>
          <dd class="mt-1 text-theme">{{ $position['principal'] }}</dd>
        </div>
        <div class="rounded-lg border border-theme bg-theme-secondary/40 p-3">
          <dt class="text-theme-secondary">{{ __('pages/positions.labels.status') }}</dt>
          <dd class="mt-1 text-theme" data-status-key="{{ $position['status'] }}">{{ $statusLabels[$position['status']] ?? $position['status'] }}</dd>
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
