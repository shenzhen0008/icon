<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>产品市场 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
  <x-nav.top />

  <main class="mx-auto w-full max-w-3xl px-4 pb-28 pt-6 md:pb-10 md:pt-8">
    <section class="mb-6 overflow-hidden rounded-3xl border border-cyan-400/20 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 p-5 shadow-xl shadow-cyan-500/10">
      <div class="space-y-4 rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-5 text-slate-200">
        <div class="flex items-center justify-between">
          <p class="text-sm text-slate-300">今日预计收益</p>
          <p class="text-xl font-semibold text-cyan-200">{{ $summary['today_profit'] }}</p>
        </div>
        <div class="h-px bg-white/10"></div>
        <div class="flex items-center justify-between">
          <p class="text-sm text-slate-300">累计收益</p>
          <p class="text-xl font-semibold text-cyan-200">{{ $summary['total_profit'] }}</p>
        </div>
        <div class="h-px bg-white/10"></div>
        <div class="flex items-center justify-between">
          <p class="text-sm text-slate-300">订单数量</p>
          <p class="text-xl font-semibold text-cyan-200">{{ $summary['orders_count'] }}</p>
        </div>
      </div>
    </section>

    <section>
      <h2 class="mb-4 text-xl font-semibold text-slate-100">自动质押</h2>

      @if (count($products) === 0)
        <section class="rounded-2xl border border-dashed border-white/20 bg-slate-900/60 p-8 text-sm text-slate-400">
          当前暂无上架产品。
        </section>
      @else
        <section class="space-y-4">
          @foreach ($products as $product)
            <article class="overflow-hidden rounded-3xl border border-cyan-400/20 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 p-4 text-slate-100 shadow-xl shadow-cyan-500/10">
              <div class="flex items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                  <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border border-cyan-300/30 bg-slate-900/70">
                    @if (!empty($product['product_icon_path']))
                      <img src="{{ $product['product_icon_path'] }}" alt="" class="h-8 w-8 object-contain">
                    @else
                      <span class="text-xs font-semibold uppercase text-emerald-700">{{ strtoupper(substr($product['code'], 0, 2)) }}</span>
                    @endif
                  </div>
                  <div class="min-w-0">
                    <h3 class="truncate text-3xl font-semibold leading-none text-white">{{ $product['name'] }}</h3>
                  </div>
                </div>
                @if ($product['purchase_limit'] !== null)
                  <p class="shrink-0 text-sm text-cyan-200">限购 <span class="font-semibold text-cyan-300">{{ $product['purchase_limit'] }}</span> 份</p>
                @endif
              </div>

              <div class="mt-4 h-px bg-white/10"></div>

              <div class="mt-4 flex items-start gap-2">
                <div class="shrink-0 text-left pr-2">
                  <p class="text-xs text-slate-400">限额(USDT)</p>
                  <p class="mt-1 whitespace-nowrap text-xl font-medium text-slate-100">{{ $product['limit_range'] }}</p>
                </div>
                <div class="min-w-0 flex-1 text-center">
                  <p class="text-xs text-slate-400">收益率</p>
                  <p class="mt-1 whitespace-nowrap text-xl font-medium text-slate-100">{{ $product['rate_range'] }}</p>
                </div>
                <div class="min-w-0 flex-1 text-right">
                  <p class="text-xs text-slate-400">周期</p>
                  <p class="mt-1 whitespace-nowrap text-xl font-medium text-slate-100">{{ $product['cycle_label'] }}</p>
                </div>
              </div>

              <div class="mt-4 rounded-2xl border border-white/10 bg-slate-900/70 px-3 py-3">
                <div class="flex flex-nowrap items-center gap-2 overflow-x-auto overflow-y-hidden whitespace-nowrap pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                  @foreach ($product['symbol_icon_paths'] as $iconPath)
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/10 bg-slate-800/70">
                      <img src="{{ $iconPath }}" alt="" class="h-7 w-7 object-contain">
                    </span>
                  @endforeach
                </div>
              </div>

              <a href="/products/{{ $product['id'] }}" class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-cyan-500 to-blue-500 px-4 py-3 text-2xl font-medium text-slate-950 shadow-lg shadow-cyan-500/30 transition hover:from-cyan-400 hover:to-blue-400">
                立即购买
              </a>
            </article>
          @endforeach
        </section>
      @endif
    </section>
  </main>

  <x-nav.mobile />
</body>
</html>
