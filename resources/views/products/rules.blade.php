<!doctype html>
<html lang="{{ __('pages/product-rules.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/product-rules.meta_title') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-1 md:pb-10 md:pt-2">
    <section>
      <div class="px-3 sm:px-5">
        <h1 class="text-scale-display font-semibold leading-tight text-theme">{{ __('pages/product-rules.hero_title') }}</h1>
        <p class="mt-2.5 max-w-3xl text-scale-body leading-7 text-theme-secondary">
          {{ __('pages/product-rules.hero_intro') }}
        </p>
      </div>
    </section>

    <section class="mt-6 rounded-[2rem] border border-[rgb(var(--theme-primary))]/20 bg-theme-card/95 p-4 shadow-2xl shadow-[rgb(var(--theme-primary))]/15 md:p-5">
      <div class="flex items-start justify-between gap-3 border-b border-theme pb-5 text-center">
        <article class="min-w-0 flex-1">
          <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full border border-[rgb(var(--theme-primary))]/25 bg-theme-secondary/20 text-[rgb(var(--theme-primary))] shadow-lg shadow-[rgb(var(--theme-primary))]/10">
            <span class="text-lg font-semibold">✓</span>
          </div>
          <h2 class="mt-3 text-scale-body font-semibold text-theme">{{ __('pages/product-rules.feature_settlement_title') }}</h2>
          <p class="mt-2 text-scale-micro leading-5 text-theme-secondary">{{ __('pages/product-rules.feature_settlement_desc') }}</p>
        </article>
        <article class="min-w-0 flex-1">
          <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full border border-[rgb(var(--theme-primary))]/25 bg-theme-secondary/20 text-[rgb(var(--theme-primary))] shadow-lg shadow-[rgb(var(--theme-primary))]/10">
            <span class="text-lg font-semibold">✓</span>
          </div>
          <h2 class="mt-3 text-scale-body font-semibold text-theme">{{ __('pages/product-rules.feature_daily_title') }}</h2>
          <p class="mt-2 text-scale-micro leading-5 text-theme-secondary">{{ __('pages/product-rules.feature_daily_desc') }}</p>
        </article>
        <article class="min-w-0 flex-1">
          <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full border border-[rgb(var(--theme-primary))]/25 bg-theme-secondary/20 text-[rgb(var(--theme-primary))] shadow-lg shadow-[rgb(var(--theme-primary))]/10">
            <span class="text-lg font-semibold">✓</span>
          </div>
          <h2 class="mt-3 text-scale-body font-semibold text-theme">{{ __('pages/product-rules.feature_return_title') }}</h2>
          <p class="mt-2 text-scale-micro leading-5 text-theme-secondary">{{ __('pages/product-rules.feature_return_desc') }}</p>
        </article>
      </div>

      <div class="space-y-7 pt-5">
        <article>
          <h2 class="text-[clamp(1.4rem,4vw,1.9rem)] font-semibold text-theme">{{ __('pages/product-rules.profit_title') }}</h2>
          <p class="mt-3 text-[clamp(1.05rem,3.8vw,1.25rem)] leading-[1.5] text-theme-secondary">
            {{ __('pages/product-rules.profit_desc') }}
          </p>
        </article>

        <article>
          <h2 class="text-[clamp(1.4rem,4vw,1.9rem)] font-semibold text-theme">{{ __('pages/product-rules.redemption_title') }}</h2>
          <p class="mt-3 text-[clamp(1.05rem,3.8vw,1.25rem)] leading-[1.5] text-theme-secondary">
            {{ __('pages/product-rules.redemption_desc') }}
          </p>
        </article>

        <article>
          <h2 class="text-[clamp(1.4rem,4vw,1.9rem)] font-semibold text-theme">{{ __('pages/product-rules.risk_title') }}</h2>
          <p class="mt-3 text-[clamp(1.05rem,3.8vw,1.25rem)] leading-[1.5] text-theme-secondary">
            {{ __('pages/product-rules.risk_desc') }}
          </p>
        </article>
      </div>
    </section>

    <section class="mt-6">
      <a
        href="/products"
        class="flex h-14 w-full items-center justify-center rounded-2xl bg-[rgb(var(--theme-primary))] px-6 text-scale-title font-semibold text-theme-on-primary shadow-xl shadow-[rgb(var(--theme-primary))]/20 transition hover:bg-[rgb(var(--theme-primary))]/90"
      >
        {{ __('pages/product-rules.go_market') }}
      </a>
    </section>
  </main>

  <x-nav.mobile />
</body>
</html>
