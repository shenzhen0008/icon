<!doctype html>
<html lang="{{ __('pages/orders.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/orders.meta_title') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-8 md:pb-10">
    <div class="space-y-5">
      <section class="rounded-2xl border border-theme bg-theme-card p-5">
        <h1 class="text-scale-title font-semibold text-theme">{{ __('pages/orders.title') }}</h1>
        <p class="mt-2 text-scale-body text-theme-secondary">{{ __('pages/orders.intro') }}</p>
      </section>

      <x-me.positions-panel :positions="$positions" />

      <section class="rounded-2xl border border-theme bg-theme-card p-5">
        @php
          $reservationStatusLabels = [
            'pending' => __('pages/orders.reservations.status.pending'),
            'approved' => __('pages/orders.reservations.status.approved'),
            'rejected' => __('pages/orders.reservations.status.rejected'),
            'converted' => __('pages/orders.reservations.status.converted'),
            'cancelled' => __('pages/orders.reservations.status.cancelled'),
          ];
        @endphp

        <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/orders.reservations.title') }}</h2>

        @if (count($reservations) === 0)
          <div class="mt-4 rounded-xl border border-dashed border-theme bg-theme-secondary/20 p-4 text-scale-body text-theme-secondary">
            {{ __('pages/orders.reservations.empty') }}
          </div>
        @else
          <ul class="mt-4 -mx-5 space-y-3 [overflow-anchor:none]">
            @foreach ($reservations as $reservation)
              <li class="rounded-xl border border-theme bg-theme-secondary/20 p-4">
                <div class="flex items-center justify-between gap-3">
                  <div class="flex items-center gap-2">
                    <span class="rounded-full border border-theme px-2 py-0.5 text-scale-micro text-theme-secondary">{{ __('pages/orders.reservations.badge') }}</span>
                    <p class="font-medium text-theme">{{ $reservation['product_name'] }}</p>
                  </div>
                  <span class="rounded-full border border-theme px-2 py-0.5 text-scale-micro text-theme-secondary">{{ $reservationStatusLabels[$reservation['status']] ?? $reservation['status'] }}</span>
                </div>

                <div class="mt-2 flex items-center justify-between gap-3 text-scale-body text-theme-secondary">
                  <p>{{ __('pages/orders.reservations.amount_prefix') }}{{ $reservation['amount_usdt'] }} USDT</p>
                  <p>{{ $reservation['created_at'] }}</p>
                </div>
              </li>
            @endforeach
          </ul>
        @endif
      </section>
    </div>
  </main>

  <x-nav.mobile />
</body>
</html>
