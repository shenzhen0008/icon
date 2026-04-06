<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $product['name'] }} | 产品详情</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
  <x-nav.top />

  <main class="mx-auto w-full max-w-3xl px-4 pb-28 pt-6 md:pb-10 md:pt-8">
    <div class="mb-6">
      <a href="/products" class="text-sm text-slate-300 underline underline-offset-4">返回产品市场</a>
    </div>

    <section class="overflow-hidden rounded-3xl border border-cyan-400/20 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 p-4 text-slate-100 shadow-xl shadow-cyan-500/10">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border border-cyan-300/30 bg-slate-900/70">
            @if (!empty($product['product_icon_path']))
              <img src="{{ $product['product_icon_path'] }}" alt="" class="h-8 w-8 object-contain">
            @else
              <span class="text-xs font-semibold uppercase text-cyan-300">{{ strtoupper(substr($product['code'], 0, 2)) }}</span>
            @endif
          </div>
          <h1 class="truncate text-3xl font-semibold">{{ $product['name'] }}</h1>
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

      @if (!empty($product['description']))
        <div class="mt-4 rounded-2xl border border-white/10 bg-slate-900/60 p-4">
          <h2 class="text-sm font-semibold text-slate-200">产品介绍</h2>
          <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-300">{{ $product['description'] }}</p>
        </div>
      @endif
    </section>

    <section class="mt-6 rounded-2xl border border-white/10 bg-slate-900/70 p-6">
      <h2 class="text-base font-semibold">购买</h2>

      @if ($isGuest)
        <p class="mt-3 text-sm text-slate-300">请先登录后购买。</p>
        <a href="/login" class="mt-4 inline-flex rounded-lg bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950">去登录</a>
      @else
        <p class="mt-3 text-sm text-slate-300">当前余额：{{ $balance }}</p>

        <form method="POST" action="/positions/purchase" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
          @csrf
          <input type="hidden" name="product_id" value="{{ $product['id'] }}">
          <div class="sm:w-48">
            <label class="mb-1 block text-xs text-slate-300">购买份数</label>
            <input type="number" min="1" step="1" name="shares" class="w-full rounded-lg border border-white/15 bg-slate-900 px-3 py-2 text-sm" required>
          </div>
          <button class="rounded-lg bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950">
            立即购买
          </button>
        </form>
        @error('shares')
          <p class="mt-3 text-sm text-rose-300">{{ $message }}</p>
        @enderror
      @endif
    </section>
  </main>

  <x-nav.mobile />
</body>
</html>
