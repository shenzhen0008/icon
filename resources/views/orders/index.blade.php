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
    </div>
  </main>

  <x-nav.mobile />
</body>
</html>
