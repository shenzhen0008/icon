<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <x-meta.theme-color />
  <title>产品市场 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-nav.top />

  <main class="mx-auto w-full max-w-7xl px-4 pb-28 pt-6 md:pb-10 md:pt-8">
    <section class="mb-6 overflow-hidden rounded-3xl border border-[rgb(var(--theme-primary))]/20 bg-gradient-to-br from-[rgb(var(--theme-primary))]/10 to-[rgb(var(--theme-accent))]/10 p-5 shadow-xl shadow-[rgb(var(--theme-primary))]/10">
      <div class="space-y-4 rounded-2xl border border-theme bg-theme-card px-4 py-5 text-theme-secondary">
        <div class="flex items-center justify-between">
          <p class="text-sm text-theme-secondary">今日预计收益</p>
          <p class="text-xl font-semibold text-[rgb(var(--theme-primary))]">{{ $summary['today_profit'] }}</p>
        </div>
        <div class="h-px bg-theme"></div>
        <div class="flex items-center justify-between">
          <p class="text-sm text-theme-secondary">累计收益</p>
          <p class="text-xl font-semibold text-[rgb(var(--theme-primary))]">{{ $summary['total_profit'] }}</p>
        </div>
        <div class="h-px bg-theme"></div>
        <div class="flex items-center justify-between">
          <p class="text-sm text-theme-secondary">订单数量</p>
          <p class="text-xl font-semibold text-[rgb(var(--theme-accent))]">{{ $summary['orders_count'] }}</p>
        </div>
      </div>
    </section>

    <section>
      <h2 class="mb-4 text-xl font-semibold text-theme">自动质押</h2>

      @if (count($products) === 0)
        <section class="rounded-2xl border border-dashed border-theme bg-theme-secondary/20 p-8 text-sm text-theme-secondary">
          当前暂无上架产品。
        </section>
      @else
        <section class="space-y-3">
          @foreach ($products as $product)
            <article class="overflow-hidden rounded-2xl border border-theme bg-theme-card p-3 text-theme shadow-xl shadow-[rgb(var(--theme-primary))]/10">
              <div class="flex items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                  <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border border-theme bg-theme-secondary/80 text-theme">
                    @if (!empty($product['product_icon_path']))
                      <img src="{{ $product['product_icon_path'] }}" alt="" class="h-8 w-8 object-contain">
                    @else
                      <span class="text-xs font-semibold uppercase text-theme">{{ strtoupper(substr($product['code'], 0, 2)) }}</span>
                    @endif
                  </div>
                  <div class="min-w-0">
                    <h3 class="truncate text-2xl font-semibold leading-none text-theme">{{ $product['name'] }}</h3>
                  </div>
                </div>
                @if ($product['purchase_limit'] !== null)
                  <p class="shrink-0 text-sm text-[rgb(var(--theme-primary))]">限购 <span class="font-semibold text-[rgb(var(--theme-accent))]">{{ $product['purchase_limit'] }}</span> 份</p>
                @endif
              </div>

              <div class="mt-3 h-px bg-theme/30"></div>

              <div class="mt-3 flex items-start gap-2">
                <div class="shrink-0 text-left pr-2">
                  <p class="text-xs text-theme-secondary">限额(USDT)</p>
                  <p class="mt-1 whitespace-nowrap text-lg font-medium text-theme">{{ $product['limit_range'] }}</p>
                </div>
                <div class="min-w-0 flex-1 text-center">
                  <p class="text-xs text-theme-secondary">收益率</p>
                  <p class="mt-1 whitespace-nowrap text-lg font-medium text-theme">{{ $product['rate_range'] }}</p>
                </div>
                <div class="min-w-0 flex-1 text-right">
                  <p class="text-xs text-theme-secondary">周期</p>
                  <p class="mt-1 whitespace-nowrap text-lg font-medium text-theme">{{ $product['cycle_label'] }}</p>
                </div>
              </div>

              <div class="mt-3 rounded-2xl border border-theme bg-theme-secondary/20 px-3 py-2">
                <div class="flex flex-nowrap items-center gap-2 overflow-x-auto overflow-y-hidden whitespace-nowrap pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                  @foreach ($product['symbol_icon_paths'] as $iconPath)
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-theme bg-theme-card">
                      <img src="{{ $iconPath }}" alt="" class="h-7 w-7 object-contain">
                    </span>
                  @endforeach
                </div>
              </div>

              <a href="/products/{{ $product['id'] }}" class="mt-3 inline-flex w-full items-center justify-center rounded-2xl bg-[rgb(var(--theme-primary))] px-4 py-2 text-xl font-medium text-theme-on-primary shadow-lg shadow-[rgb(var(--theme-primary))]/20 transition hover:bg-[rgb(var(--theme-primary))]/90">
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
