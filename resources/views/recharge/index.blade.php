<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>充值 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-nav.top />

  <main class="mx-auto w-full max-w-7xl px-4 pb-28 pt-8 md:pb-10">
    <section class="rounded-3xl border border-[rgb(var(--theme-primary))]/20 bg-gradient-to-br from-[rgb(var(--theme-primary))]/10 to-[rgb(var(--theme-accent))]/10 p-6 shadow-xl shadow-[rgb(var(--theme-primary))]/10">
      <h1 class="text-2xl font-semibold text-theme">充值</h1>
      <p class="mt-3 text-theme-secondary">充值入口正在开发中，后续会为你开放快速充值和余额管理功能。</p>

      <div class="mt-6 grid gap-4 md:grid-cols-2">
        <div class="rounded-2xl border border-theme bg-theme-card p-4">
          <p class="text-sm text-theme-secondary">功能状态</p>
          <p class="mt-2 text-lg font-semibold text-[rgb(var(--theme-primary))]">测试中</p>
        </div>
        <div class="rounded-2xl border border-theme bg-theme-card p-4">
          <p class="text-sm text-theme-secondary">说明</p>
          <p class="mt-2 text-sm text-theme-secondary">请稍后关注此页面，正式充值功能上线前你仍可通过客服获取帮助。</p>
        </div>
      </div>

      <div class="mt-6 rounded-2xl border border-theme bg-theme-secondary/70 p-4 text-sm text-theme-secondary">
        <p class="font-medium text-theme">后续支持</p>
        <ul class="mt-3 space-y-2 list-disc pl-5">
          <li>余额充值与支付通道接入</li>
          <li>充值记录与流水查询</li>
          <li>充值优惠与活动入口</li>
        </ul>
      </div>
    </section>
  </main>

  <x-nav.mobile />
</body>
</html>
