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

  <main class="mx-auto w-full max-w-4xl px-6 pb-28 pt-8 md:pb-10">
    <div class="mb-8">
      <a href="/products" class="text-sm text-slate-300 underline underline-offset-4">返回产品市场</a>
    </div>

    <section class="rounded-2xl border border-cyan-400/20 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">{{ $product['name'] }}</h1>
        <span class="rounded-full border border-white/20 px-2 py-0.5 text-xs text-slate-300">{{ $product['code'] }}</span>
      </div>
      <div class="mt-6 grid gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-white/10 bg-slate-950/40 p-4">
          <p class="text-sm text-slate-400">每份价格</p>
          <p class="mt-1 text-xl font-semibold text-cyan-200">{{ $product['unit_price'] }}</p>
        </div>
      </div>
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
            确认购买
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
