<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>订单 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-8 md:pb-10">
    <div class="space-y-5">
      <section class="rounded-2xl border border-theme bg-theme-card p-5">
        <h1 class="text-scale-title font-semibold text-theme">订单</h1>
        <p class="mt-2 text-scale-body text-theme-secondary">查看当前持仓与最近收益记录。</p>
      </section>

      <x-me.positions-panel :positions="$positions" />

      <section class="rounded-2xl border border-theme bg-theme-card p-5">
        @php
          $reservationStatusLabels = [
            'pending' => '待审核',
            'approved' => '审核通过',
            'rejected' => '已拒绝',
            'converted' => '已转购买',
            'cancelled' => '已取消',
          ];
        @endphp

        <h2 class="text-scale-body font-semibold text-theme">预订订单</h2>

        @if (count($reservations) === 0)
          <div class="mt-4 rounded-xl border border-dashed border-theme bg-theme-secondary/20 p-4 text-scale-body text-theme-secondary">
            暂无预订订单
          </div>
        @else
          <ul class="mt-4 -mx-5 space-y-3 [overflow-anchor:none]">
            @foreach ($reservations as $reservation)
              <li class="rounded-xl border border-theme bg-theme-secondary/20 p-4">
                <div class="flex items-center justify-between gap-3">
                  <div class="flex items-center gap-2">
                    <span class="rounded-full border border-theme px-2 py-0.5 text-scale-micro text-theme-secondary">预订订单</span>
                    <p class="font-medium text-theme">{{ $reservation['product_name'] }}</p>
                  </div>
                  <span class="rounded-full border border-theme px-2 py-0.5 text-scale-micro text-theme-secondary">{{ $reservationStatusLabels[$reservation['status']] ?? $reservation['status'] }}</span>
                </div>

                <div class="mt-2 flex items-center justify-between gap-3 text-scale-body text-theme-secondary">
                  <p>金额：{{ $reservation['amount_usdt'] }} USDT</p>
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
