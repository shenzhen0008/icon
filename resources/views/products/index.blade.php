<!doctype html>
<html lang="{{ __('pages/product-list.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/product-list.meta_title') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  @php
    $localeQuery = 'locale='.urlencode(app()->getLocale());
  @endphp
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-6 md:pb-10 md:pt-8">
    <section class="mb-6 overflow-hidden rounded-3xl border border-[rgb(var(--theme-primary))]/20 bg-gradient-to-br from-[rgb(var(--theme-primary))]/10 to-[rgb(var(--theme-accent))]/10 p-5 shadow-xl shadow-[rgb(var(--theme-primary))]/10">
      <div class="mb-4 grid grid-cols-2 gap-3">
        <a
          href="/products/rules"
          data-keep-locale
          class="flex h-12 items-center justify-center rounded-2xl border border-[rgb(var(--theme-primary))]/20 bg-theme-card px-4 text-scale-body font-semibold text-theme shadow-lg shadow-[rgb(var(--theme-primary))]/10 transition hover:border-[rgb(var(--theme-primary))]/40 hover:text-[rgb(var(--theme-primary))]"
        >
          {{ __('pages/product-list.rules') }}
        </a>
        <a
          href="/me/orders"
          data-keep-locale
          class="flex h-12 items-center justify-center rounded-2xl border border-[rgb(var(--theme-primary))]/20 bg-theme-card px-4 text-scale-body font-semibold text-theme shadow-lg shadow-[rgb(var(--theme-primary))]/10 transition hover:border-[rgb(var(--theme-primary))]/40 hover:text-[rgb(var(--theme-primary))]"
        >
          {{ __('pages/product-list.orders') }}
        </a>
      </div>

      <div class="space-y-4 rounded-2xl border border-theme bg-theme-card px-4 py-5 text-theme-secondary">
        <div class="flex items-center justify-between">
          <p class="text-scale-body text-theme-secondary">{{ __('pages/product-list.today_profit') }}</p>
          <p class="text-scale-title font-semibold text-[rgb(var(--theme-primary))]">{{ $summary['today_profit'] }}</p>
        </div>
        <div class="h-px bg-theme"></div>
        <div class="flex items-center justify-between">
          <p class="text-scale-body text-theme-secondary">{{ __('pages/product-list.total_profit') }}</p>
          <p class="text-scale-title font-semibold text-[rgb(var(--theme-primary))]">{{ $summary['total_profit'] }}</p>
        </div>
        <div class="h-px bg-theme"></div>
        <div class="flex items-center justify-between">
          <p class="text-scale-body text-theme-secondary">{{ __('pages/product-list.orders_count') }}</p>
          <p class="text-scale-title font-semibold text-[rgb(var(--theme-accent))]">{{ $summary['orders_count'] }}</p>
        </div>
      </div>
    </section>

    <section>
      <h2 class="mb-4 text-scale-title font-semibold text-theme">{{ __('pages/product-list.section_title') }}</h2>

      @if (count($products) === 0)
        <section class="rounded-2xl border border-dashed border-theme bg-theme-secondary/20 p-8 text-scale-body text-theme-secondary">
          {{ __('pages/product-list.empty_state') }}
        </section>
      @else
        <section class="space-y-3">
          @foreach ($products as $product)
            <article class="overflow-hidden rounded-2xl border border-theme bg-theme-card p-2 text-theme shadow-xl shadow-[rgb(var(--theme-primary))]/10">
              <div class="flex items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-2">
                  <div class="flex h-[clamp(1.75rem,6vw,2.25rem)] w-[clamp(1.75rem,6vw,2.25rem)] shrink-0 items-center justify-center overflow-hidden rounded-full border border-theme bg-theme-secondary/80 text-theme">
                    @if (!empty($product['product_icon_path']))
                      <img src="{{ $product['product_icon_path'] }}" alt="" class="h-[clamp(1.125rem,4vw,1.5rem)] w-[clamp(1.125rem,4vw,1.5rem)] object-contain">
                    @else
                      <span class="text-scale-micro font-semibold uppercase text-theme">{{ strtoupper(substr($product['code'], 0, 2)) }}</span>
                    @endif
                  </div>
                  <div class="min-w-0">
                    <h3 class="text-scale-title truncate font-semibold leading-none text-theme">{{ $product['name'] }}</h3>
                  </div>
                </div>
                <span class="shrink-0 rounded-full border border-[rgb(var(--theme-primary))]/30 bg-[rgb(var(--theme-primary))]/10 px-2 py-1 text-scale-micro font-semibold text-[rgb(var(--theme-primary))]">
                  {{ __('pages/product-list.purchase_limit_prefix') }}{{ $product['purchase_limit_label'] }}
                </span>
              </div>

              <div class="mt-3 h-px bg-theme/30"></div>

              <div class="mt-3 grid grid-cols-3 items-start gap-2">
                <div class="min-w-0 text-left">
                  <p class="text-scale-micro text-theme-secondary">{{ __('pages/product-list.amount_usdt') }}</p>
                  <p class="text-scale-ui mt-1 whitespace-nowrap font-medium text-theme">{{ $product['limit_range'] }}</p>
                </div>
                <div class="min-w-0 text-center">
                  <p class="text-scale-micro text-theme-secondary">{{ __('pages/product-list.yield_rate') }}</p>
                  <p class="text-scale-ui mt-1 whitespace-nowrap font-medium text-theme">{{ $product['rate_range'] }}</p>
                </div>
                <div class="min-w-0 text-right">
                  <p class="text-scale-micro text-theme-secondary">{{ __('pages/product-list.cycle') }}</p>
                  <p class="text-scale-ui mt-1 whitespace-nowrap font-medium text-theme">{{ $product['cycle_label'] }}</p>
                </div>
              </div>

              <div class="mt-2.5 rounded-2xl border border-theme bg-theme-secondary/20 px-2 py-[0.1rem]">
                <div class="flex flex-nowrap items-center gap-2 overflow-x-auto overflow-y-hidden whitespace-nowrap pb-[0.05rem] [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                  @foreach ($product['symbol_icon_paths'] as $iconPath)
                    <span class="flex h-[clamp(1.95rem,6.8vw,2.45rem)] w-[clamp(1.95rem,6.8vw,2.45rem)] shrink-0 items-center justify-center overflow-hidden rounded-full border border-theme bg-theme-card">
                      <img src="{{ $iconPath }}" alt="" class="h-[clamp(1.45rem,5.2vw,1.82rem)] w-[clamp(1.45rem,5.2vw,1.82rem)] object-contain">
                    </span>
                  @endforeach
                </div>
              </div>

              @if ($isGuest)
                <button
                  type="button"
                  data-open-activate-modal
                  class="text-scale-ui mt-2.5 flex h-[clamp(1.9rem,7vw,2.2rem)] min-w-[clamp(7rem,42vw,9rem)] w-auto items-center justify-center whitespace-nowrap rounded-2xl bg-[rgb(var(--theme-primary))] px-[clamp(0.6rem,2.5vw,0.9rem)] font-semibold text-theme-on-primary shadow-lg shadow-[rgb(var(--theme-primary))]/20 transition hover:bg-[rgb(var(--theme-primary))]/90 mx-auto"
                >
                  {{ $product['trade_mode'] === 'reserve' ? __('pages/product-list.preorder_now') : __('pages/product-list.buy_now') }}
                </button>
              @else
                <a href="/products/{{ $product['id'] }}?{{ $localeQuery }}" class="text-scale-ui mt-2.5 flex h-[clamp(1.9rem,7vw,2.2rem)] min-w-[clamp(7rem,42vw,9rem)] w-auto items-center justify-center whitespace-nowrap rounded-2xl bg-[rgb(var(--theme-primary))] px-[clamp(0.6rem,2.5vw,0.9rem)] font-semibold text-theme-on-primary shadow-lg shadow-[rgb(var(--theme-primary))]/20 transition hover:bg-[rgb(var(--theme-primary))]/90 mx-auto">
                  {{ $product['trade_mode'] === 'reserve' ? __('pages/product-list.preorder_now') : __('pages/product-list.buy_now') }}
                </a>
              @endif
            </article>
          @endforeach
        </section>
      @endif
    </section>
  </main>

  @if ($isGuest)
    <button id="open-activate-modal" type="button" class="hidden" aria-hidden="true">open</button>
    <x-auth.activate-pin-modal
      modal-id="activate-modal"
      open-button-id="open-activate-modal"
      :redirect-to="'/products?'.$localeQuery"
      :login-url="'/login?redirect_to=%2Fproducts&'.$localeQuery"
      :login-label="__('pages/me.activate_modal.login')"
      :invite-code="app(\App\Modules\Referral\Support\InviteCodeResolver::class)->currentForForm(request())"
    />
    <script>
      document.querySelectorAll('[data-open-activate-modal]').forEach((node) => {
        node.addEventListener('click', () => {
          document.getElementById('open-activate-modal')?.click();
        });
      });
    </script>
  @endif

  <x-nav.mobile />
</body>
</html>
