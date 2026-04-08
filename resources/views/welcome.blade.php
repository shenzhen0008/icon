<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <x-meta.theme-color />
  <title>Icon Market | 数字资产管理平台</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <div class="absolute inset-0 -z-10 overflow-hidden">
    <div class="absolute -top-24 left-1/2 h-80 w-80 -translate-x-1/2 rounded-full bg-[rgb(var(--theme-primary))]/20 blur-3xl"></div>
    <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-[rgb(var(--theme-accent))]/20 blur-3xl"></div>
  </div>

  <x-nav.top />

  <main class="mx-auto w-full max-w-7xl px-2 pb-28 pt-8 md:pb-12">
    <x-home.hero :summary="$summary" />
    <x-home.stats :summary="$summary" />
    <x-home.exchange-metrics :metrics="$metrics" />
  </main>

  <x-nav.mobile />
</body>
</html>
