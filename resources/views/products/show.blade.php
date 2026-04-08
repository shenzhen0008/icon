<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <x-meta.theme-color />
  <title>{{ $product['name'] }} | 产品详情</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-nav.top />

  <main class="mx-auto w-full max-w-3xl px-4 pb-28 pt-6 md:pb-10 md:pt-8">
    <div class="mb-6">
      <a href="/products" class="text-sm text-theme-secondary underline underline-offset-4">返回产品市场</a>
    </div>

    <section class="overflow-hidden rounded-3xl border border-theme bg-theme-card p-4 text-theme shadow-xl shadow-[rgb(var(--theme-primary))]/10">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border border-theme bg-theme-secondary/70 text-theme-secondary">
            @if (!empty($product['product_icon_path']))
              <img src="{{ $product['product_icon_path'] }}" alt="" class="h-8 w-8 object-contain">
            @else
              <span class="text-xs font-semibold uppercase text-[rgb(var(--theme-primary))]">{{ strtoupper(substr($product['code'], 0, 2)) }}</span>
            @endif
          </div>
          <h1 class="truncate text-3xl font-semibold">{{ $product['name'] }}</h1>
        </div>
        @if ($product['purchase_limit'] !== null)
          <p class="shrink-0 text-sm text-theme-secondary">限购 <span class="font-semibold text-[rgb(var(--theme-primary))]">{{ $product['purchase_limit'] }}</span> 份</p>
        @endif
      </div>

      <div class="mt-4 h-px bg-theme/20"></div>

      <div class="mt-4 flex items-start gap-2">
        <div class="shrink-0 text-left pr-2">
          <p class="text-xs text-theme-secondary">限额(USDT)</p>
          <p class="mt-1 whitespace-nowrap text-xl font-medium text-theme">{{ $product['limit_range'] }}</p>
        </div>
        <div class="min-w-0 flex-1 text-center">
          <p class="text-xs text-theme-secondary">收益率</p>
          <p class="mt-1 whitespace-nowrap text-xl font-medium text-theme">{{ $product['rate_range'] }}</p>
        </div>
        <div class="min-w-0 flex-1 text-right">
          <p class="text-xs text-theme-secondary">周期</p>
          <p class="mt-1 whitespace-nowrap text-xl font-medium text-theme">{{ $product['cycle_label'] }}</p>
        </div>
      </div>

      <div class="mt-4 rounded-2xl border border-theme bg-theme-secondary/70 px-3 py-3">
        <div class="flex flex-nowrap items-center gap-2 overflow-x-auto overflow-y-hidden whitespace-nowrap pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
          @foreach ($product['symbol_icon_paths'] as $iconPath)
            <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-theme bg-theme-card text-theme-secondary">
              <img src="{{ $iconPath }}" alt="" class="h-7 w-7 object-contain">
            </span>
          @endforeach
        </div>
      </div>

      @if (!empty($product['description']))
        <div class="mt-4 rounded-2xl border border-theme bg-theme-card p-4">
          <h2 class="text-sm font-semibold text-theme">产品介绍</h2>
          <p class="mt-2 whitespace-pre-line text-sm leading-6 text-theme-secondary">{{ $product['description'] }}</p>
        </div>
      @endif
    </section>

    <section class="mt-6 rounded-2xl border border-theme bg-theme-card p-6">
      <h2 class="text-base font-semibold text-theme">购买</h2>

      @if ($isGuest)
        <p class="mt-3 text-sm text-theme-secondary">请先登录后购买。</p>
        <a href="/login" class="mt-4 inline-flex rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2 text-sm font-semibold text-theme-secondary">去登录</a>
      @else
        <p class="mt-3 text-sm text-theme-secondary">当前余额：{{ $balance }}</p>

        <form method="POST" action="/positions/purchase" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
          @csrf
          <input type="hidden" name="product_id" value="{{ $product['id'] }}">
          <div class="sm:w-48">
            <label class="mb-1 block text-xs text-theme-secondary">购买份数</label>
            <input type="number" min="1" step="1" name="shares" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-sm text-theme" required>
          </div>
          <button class="rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2 text-sm font-semibold text-theme-on-primary">
            立即购买
          </button>
        </form>
        @error('shares')
          <p class="mt-3 text-sm text-[rgb(var(--theme-rose))]">{{ $message }}</p>
        @enderror
      @endif
    </section>
  </main>

  <x-nav.mobile />
</body>
</html>
