<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Icon Market | 数字资产管理平台</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
  <div class="absolute inset-0 -z-10 overflow-hidden">
    <div class="absolute -top-24 left-1/2 h-80 w-80 -translate-x-1/2 rounded-full bg-cyan-500/20 blur-3xl"></div>
    <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-blue-600/20 blur-3xl"></div>
  </div>

  <x-nav.top />

  <main class="mx-auto w-full max-w-6xl px-6 pb-28 pt-8 md:pb-12">
    <x-home.hero :summary="$summary" />
    <x-home.stats :summary="$summary" />
    <x-home.exchange-metrics :metrics="$metrics" />
  </main>

  <x-nav.mobile />
</body>
</html>
