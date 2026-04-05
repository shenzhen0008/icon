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

  <main class="mx-auto w-full max-w-6xl px-6 pb-28 pt-8 md:pb-10">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
      <div>
        <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">Products</p>
        <h1 class="mt-2 text-2xl font-semibold">产品市场</h1>
        <p class="mt-1 text-sm text-slate-400">公开展示当前可购买产品与每份价格。</p>
      </div>
    </div>

    @if (count($products) === 0)
      <section class="rounded-2xl border border-dashed border-white/20 bg-slate-900/60 p-8 text-sm text-slate-400">
        当前暂无上架产品。
      </section>
    @else
      <section class="grid gap-4 md:grid-cols-2">
        @foreach ($products as $product)
          <article class="rounded-2xl border border-cyan-400/20 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 p-6">
            <div class="flex items-center justify-between gap-2">
              <h2 class="text-lg font-semibold text-white">{{ $product['name'] }}</h2>
              <span class="rounded-full border border-white/20 px-2 py-0.5 text-xs text-slate-300">{{ $product['code'] }}</span>
            </div>
            <p class="mt-4 text-sm text-slate-300">每份价格</p>
            <p class="mt-1 text-xl font-semibold text-cyan-200">{{ $product['unit_price'] }}</p>
            <a href="/products/{{ $product['id'] }}" class="mt-4 inline-flex rounded-lg bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950">查看详情</a>
          </article>
        @endforeach
      </section>
    @endif
  </main>

  <x-nav.mobile />
</body>
</html>
