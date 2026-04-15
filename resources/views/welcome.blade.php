<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>Icon Market | 数字资产管理平台</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />

  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-8 md:pb-12">
    <x-home.hero :summary="$summary" />
    <x-home.stats :summary="$summary" />
    <x-home.exchange-metrics :metrics="$metrics" />
  </main>

  <x-nav.mobile />
</body>
</html>
