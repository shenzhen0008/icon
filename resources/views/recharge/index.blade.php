<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>充值 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
  <x-nav.top />

  <main class="mx-auto w-full max-w-7xl px-4 pb-28 pt-8 md:pb-10">
    <section class="rounded-3xl border border-cyan-400/20 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 p-6 shadow-xl shadow-cyan-500/10">
      <h1 class="text-2xl font-semibold text-white">充值</h1>
      <p class="mt-3 text-slate-400">充值入口正在开发中，后续会为你开放快速充值和余额管理功能。</p>

      <div class="mt-6 grid gap-4 md:grid-cols-2">
        <div class="rounded-2xl border border-white/10 bg-slate-950/60 p-4">
          <p class="text-sm text-slate-400">功能状态</p>
          <p class="mt-2 text-lg font-semibold text-cyan-200">测试中</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-slate-950/60 p-4">
          <p class="text-sm text-slate-400">说明</p>
          <p class="mt-2 text-sm text-slate-300">请稍后关注此页面，正式充值功能上线前你仍可通过客服获取帮助。</p>
        </div>
      </div>

      <div class="mt-6 rounded-2xl border border-white/10 bg-slate-900/70 p-4 text-sm text-slate-400">
        <p class="font-medium text-slate-100">后续支持</p>
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
