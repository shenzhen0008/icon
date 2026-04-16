<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>收入记录 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-8 md:pb-10">
    <section class="rounded-2xl border border-theme bg-theme-card p-5">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h1 class="text-scale-title font-semibold text-theme">收入记录</h1>
          <p class="mt-2 text-scale-body text-theme-secondary">首页组件专属收入记录页面（{{ $mode === 'demo' ? 'DEMO' : 'LIVE' }}）。</p>
        </div>
        <a href="/" class="rounded-lg border border-theme px-3 py-2 text-scale-body text-theme-secondary hover:text-theme">返回首页</a>
      </div>
    </section>

    <section class="mt-5 overflow-hidden rounded-2xl border border-theme bg-theme-card">
      <table class="min-w-full text-scale-body">
        <thead class="bg-theme-secondary/80 text-theme-secondary">
          <tr>
            <th class="px-4 py-3 text-left font-medium">产品</th>
            <th class="px-4 py-3 text-right font-medium">收益(USDT)</th>
            <th class="px-4 py-3 text-right font-medium">收益率</th>
            <th class="px-4 py-3 text-right font-medium">结算时间</th>
          </tr>
        </thead>
        <tbody class="bg-theme-secondary/40">
          @forelse ($records as $record)
            <tr class="border-t border-theme">
              <td class="px-4 py-3 text-theme">{{ $record['product_name'] ?? '--' }}</td>
              <td class="px-4 py-3 text-right text-emerald-300">{{ $record['profit'] ?? '0.00' }}</td>
              <td class="px-4 py-3 text-right text-theme-secondary">{{ $record['rate_percent'] ?? '--' }}</td>
              <td class="px-4 py-3 text-right text-theme-secondary">{{ $record['settlement_at'] ?? '--' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-4 py-6 text-center text-theme-secondary">暂无收入记录</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </section>

    @if ($pagination?->hasPages())
      <div class="mt-4">{{ $pagination->links() }}</div>
    @endif
  </main>

  <x-nav.mobile />
</body>
</html>
