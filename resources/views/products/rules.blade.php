<!doctype html>
<html lang="{{ __('pages/product-rules.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/product-rules.meta_title', ['app_name' => config('app.name')]) }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  @php
    $localeQuery = 'locale='.urlencode(app()->getLocale());
  @endphp
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-24 pt-1 md:pb-8 md:pt-2">
    <section class="rounded-2xl border border-white/20 bg-[rgba(9,28,86,0.52)] px-2 py-3 shadow-lg shadow-[rgba(7,21,66,0.25)] backdrop-blur-sm sm:px-4">
      <div>
        <h1 class="text-scale-title font-semibold leading-tight text-white drop-shadow-[0_1px_2px_rgba(0,0,0,0.35)]">{{ __('pages/product-rules.hero_title') }}</h1>
        <p class="mt-2 max-w-3xl text-scale-micro leading-5 text-white/90">
          {{ __('pages/product-rules.hero_intro') }}
        </p>
      </div>
    </section>

    <section class="mt-3 rounded-2xl border border-theme bg-theme-card p-3 shadow-xl shadow-[rgb(var(--theme-primary))]/12 md:p-4">
      <div class="flex items-start justify-between gap-2 border-b border-theme pb-3 text-center">
        <article class="min-w-0 flex-1">
          <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-full border border-[rgb(var(--theme-primary))]/25 bg-theme-secondary/20 text-[rgb(var(--theme-primary))] shadow-md shadow-[rgb(var(--theme-primary))]/10">
            <span class="text-scale-body font-semibold">✓</span>
          </div>
          <h2 class="mt-1.5 text-scale-micro font-semibold text-theme">{{ __('pages/product-rules.feature_settlement_title') }}</h2>
          <p class="mt-1 text-[0.68rem] leading-4 text-theme">{{ __('pages/product-rules.feature_settlement_desc') }}</p>
        </article>
        <article class="min-w-0 flex-1">
          <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-full border border-[rgb(var(--theme-primary))]/25 bg-theme-secondary/20 text-[rgb(var(--theme-primary))] shadow-md shadow-[rgb(var(--theme-primary))]/10">
            <span class="text-scale-body font-semibold">✓</span>
          </div>
          <h2 class="mt-1.5 text-scale-micro font-semibold text-theme">{{ __('pages/product-rules.feature_daily_title') }}</h2>
          <p class="mt-1 text-[0.68rem] leading-4 text-theme">{{ __('pages/product-rules.feature_daily_desc') }}</p>
        </article>
        <article class="min-w-0 flex-1">
          <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-full border border-[rgb(var(--theme-primary))]/25 bg-theme-secondary/20 text-[rgb(var(--theme-primary))] shadow-md shadow-[rgb(var(--theme-primary))]/10">
            <span class="text-scale-body font-semibold">✓</span>
          </div>
          <h2 class="mt-1.5 text-scale-micro font-semibold text-theme">{{ __('pages/product-rules.feature_return_title') }}</h2>
          <p class="mt-1 text-[0.68rem] leading-4 text-theme">{{ __('pages/product-rules.feature_return_desc') }}</p>
        </article>
      </div>

      <div class="space-y-3 pt-3">
        <article>
          <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/product-rules.profit_title') }}</h2>
          <p class="mt-1 text-scale-micro leading-5 text-theme">
            {{ __('pages/product-rules.profit_desc') }}
          </p>
        </article>

        <article>
          <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/product-rules.redemption_title') }}</h2>
          <p class="mt-1 text-scale-micro leading-5 text-theme">
            {{ __('pages/product-rules.redemption_desc') }}
          </p>
        </article>

        <article>
          <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/product-rules.risk_title') }}</h2>
          <p class="mt-1 text-scale-micro leading-5 text-theme">
            {{ __('pages/product-rules.risk_desc') }}
          </p>
        </article>
      </div>
    </section>

    <section class="mt-3">
      <a
        href="/products?{{ $localeQuery }}"
        class="flex h-11 w-full items-center justify-center rounded-xl bg-[rgb(var(--theme-primary))] px-5 text-scale-body font-semibold text-theme-on-primary shadow-lg shadow-[rgb(var(--theme-primary))]/20 transition hover:bg-[rgb(var(--theme-primary))]/90"
      >
        {{ __('pages/product-rules.go_market') }}
      </a>
    </section>
  </main>

  <x-nav.mobile />
</body>
</html>
