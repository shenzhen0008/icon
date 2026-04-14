<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>规则说明 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-1 md:pb-10 md:pt-2">
    <section>
      <div class="px-3 sm:px-5">
        <h1 class="text-scale-display font-semibold leading-tight text-theme">AI Trading</h1>
        <p class="mt-2.5 max-w-3xl text-scale-body leading-7 text-theme-secondary">
          系统基于企业级 AI 策略模型执行量化处理、自动学习与参数优化，在规则范围内提升委托效率，并将收益结算结果同步到订单记录中。
        </p>
      </div>
    </section>

    <section class="mt-6 rounded-[2rem] border border-[rgb(var(--theme-primary))]/20 bg-theme-card/95 p-4 shadow-2xl shadow-[rgb(var(--theme-primary))]/15 md:p-5">
      <div class="flex items-start justify-between gap-3 border-b border-theme pb-5 text-center">
        <article class="min-w-0 flex-1">
          <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full border border-[rgb(var(--theme-primary))]/25 bg-theme-secondary/20 text-[rgb(var(--theme-primary))] shadow-lg shadow-[rgb(var(--theme-primary))]/10">
            <span class="text-lg font-semibold">✓</span>
          </div>
          <h2 class="mt-3 text-scale-body font-semibold text-theme">安全结算</h2>
          <p class="mt-2 text-scale-micro leading-5 text-theme-secondary">收益记录透明可查</p>
        </article>
        <article class="min-w-0 flex-1">
          <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full border border-[rgb(var(--theme-primary))]/25 bg-theme-secondary/20 text-[rgb(var(--theme-primary))] shadow-lg shadow-[rgb(var(--theme-primary))]/10">
            <span class="text-lg font-semibold">✓</span>
          </div>
          <h2 class="mt-3 text-scale-body font-semibold text-theme">每日执行</h2>
          <p class="mt-2 text-scale-micro leading-5 text-theme-secondary">持续运行无需盯盘</p>
        </article>
        <article class="min-w-0 flex-1">
          <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full border border-[rgb(var(--theme-primary))]/25 bg-theme-secondary/20 text-[rgb(var(--theme-primary))] shadow-lg shadow-[rgb(var(--theme-primary))]/10">
            <span class="text-lg font-semibold">✓</span>
          </div>
          <h2 class="mt-3 text-scale-body font-semibold text-theme">自动返还</h2>
          <p class="mt-2 text-scale-micro leading-5 text-theme-secondary">到期后本金自动回账</p>
        </article>
      </div>

      <div class="space-y-7 pt-5">
        <article>
          <h2 class="text-[clamp(1.4rem,4vw,1.9rem)] font-semibold text-theme">收益说明</h2>
          <p class="mt-3 text-[clamp(1.05rem,3.8vw,1.25rem)] leading-[1.5] text-theme-secondary">
            委托生效后，系统将按照所选产品的收益区间和周期规则自动进行结算，当前结算收益会根据委托的策略产品同步更新到订单详情。
          </p>
        </article>

        <article>
          <h2 class="text-[clamp(1.4rem,4vw,1.9rem)] font-semibold text-theme">赎回规则</h2>
          <p class="mt-3 text-[clamp(1.05rem,3.8vw,1.25rem)] leading-[1.5] text-theme-secondary">
            在委托订单完成前，如需结束当前持仓，可在订单详情页提交赎回申请。赎回审核期间，系统将暂停该持仓继续产生收益；当订单自然结束或赎回通过后，本金会自动返还到账户余额。
          </p>
        </article>

        <article>
          <h2 class="text-[clamp(1.4rem,4vw,1.9rem)] font-semibold text-theme">风险提示</h2>
          <p class="mt-3 text-[clamp(1.05rem,3.8vw,1.25rem)] leading-[1.5] text-theme-secondary">
            数字资产策略存在波动与执行风险，历史收益表现不代表未来结果。参与前请结合自身风险承受能力，合理安排委托规模。
          </p>
        </article>
      </div>
    </section>

    <section class="mt-6">
      <a
        href="/products"
        class="flex h-14 w-full items-center justify-center rounded-2xl bg-[rgb(var(--theme-primary))] px-6 text-scale-title font-semibold text-theme-on-primary shadow-xl shadow-[rgb(var(--theme-primary))]/20 transition hover:bg-[rgb(var(--theme-primary))]/90"
      >
        前往产品市场
      </a>
    </section>
  </main>

  <x-nav.mobile />
</body>
</html>
