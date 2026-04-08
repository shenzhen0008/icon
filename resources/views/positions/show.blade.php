<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <x-meta.theme-color />
  <title>订单详情 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-nav.top />

  @php
    $statusLabels = [
      'open' => '持有中',
      'redeeming' => '赎回中',
      'redeemed' => '已赎回',
    ];
  @endphp

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-6 md:pb-10 md:pt-8">
    <section class="rounded-2xl border border-theme bg-theme-card p-5">
      <h1 class="text-xl font-semibold">订单详情</h1>

      @if ($can_apply_redemption)
        <form method="POST" action="/me/positions/{{ $position['id'] }}/redemption-requests" class="mt-4" onsubmit="return confirm('产品赎回后产品价值将会回到账户余额不会得到收益');">
          @csrf
          <button class="rounded-lg bg-[rgb(var(--theme-rose))] px-4 py-2 text-sm font-semibold text-theme-secondary">申请赎回</button>
        </form>
      @elseif ($redemption_request_status === 'pending')
        <p class="mt-4 text-sm text-amber-300">赎回申请待审核，当前持仓已暂停收益。</p>
      @elseif ($position['status'] === 'redeemed')
        <p class="mt-4 text-sm text-emerald-300">该持仓已赎回完成。</p>
      @endif

      @error('position')
        <p class="mt-3 text-sm text-[rgb(var(--theme-rose))]">{{ $message }}</p>
      @enderror

      <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
        <div class="rounded-lg border border-theme bg-theme-secondary/40 p-3">
          <dt class="text-theme-secondary">订单ID</dt>
          <dd class="mt-1 text-theme">{{ $position['id'] }}</dd>
        </div>
        <div class="rounded-lg border border-theme bg-theme-secondary/40 p-3">
          <dt class="text-theme-secondary">产品</dt>
          <dd class="mt-1 text-theme">{{ $position['product_name'] }}</dd>
        </div>
        <div class="rounded-lg border border-theme bg-theme-secondary/40 p-3">
          <dt class="text-theme-secondary">本金</dt>
          <dd class="mt-1 text-theme">{{ $position['principal'] }}</dd>
        </div>
        <div class="rounded-lg border border-theme bg-theme-secondary/40 p-3">
          <dt class="text-theme-secondary">状态</dt>
          <dd class="mt-1 text-theme" data-status-key="{{ $position['status'] }}">{{ $statusLabels[$position['status']] ?? $position['status'] }}</dd>
        </div>
      </dl>
    </section>

    <section class="mt-6 rounded-2xl border border-theme bg-theme-card p-5">
      <h2 class="text-base font-semibold">每日收益</h2>

      @if (count($daily_profits) === 0)
        <div class="mt-4 rounded-xl border border-dashed border-theme bg-theme-secondary/40 p-4 text-sm text-theme-secondary">
          暂无每日收益记录
        </div>
      @else
        <div class="mt-4 overflow-hidden rounded-xl border border-theme">
          <table class="min-w-full text-sm">
            <thead class="bg-theme-secondary/80 text-theme-secondary">
              <tr>
                <th class="px-4 py-3 text-left font-medium">结算日期</th>
                <th class="px-4 py-3 text-right font-medium">日收益率</th>
                <th class="px-4 py-3 text-right font-medium">当日收益</th>
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
