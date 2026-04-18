<!doctype html>
<html lang="{{ __('pages/product-detail.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ $product['name'] }} | {{ __('pages/product-detail.meta_title_suffix') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-6 md:pb-10 md:pt-8">
    <section class="overflow-hidden rounded-3xl border border-theme bg-theme-card p-2.5 text-theme shadow-xl shadow-[rgb(var(--theme-primary))]/10">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-2">
          <div class="flex h-[clamp(1.75rem,6vw,2.25rem)] w-[clamp(1.75rem,6vw,2.25rem)] shrink-0 items-center justify-center overflow-hidden rounded-full border border-theme bg-theme-secondary/70 text-theme-secondary">
            @if (!empty($product['product_icon_path']))
              <img src="{{ $product['product_icon_path'] }}" alt="" class="h-[clamp(1.125rem,4vw,1.5rem)] w-[clamp(1.125rem,4vw,1.5rem)] object-contain">
            @else
              <span class="text-scale-micro font-semibold uppercase text-[rgb(var(--theme-primary))]">{{ strtoupper(substr($product['code'], 0, 2)) }}</span>
            @endif
          </div>
          <h1 class="text-scale-title truncate font-semibold">{{ $product['name'] }}</h1>
        </div>
      </div>

      <div class="mt-4 h-px bg-theme/20"></div>

      <div class="mt-4 flex items-start gap-2">
        <div class="min-w-0 flex-1 text-left">
          <p class="text-scale-micro text-theme-secondary">{{ __('pages/product-detail.amount_usdt') }}</p>
          <p class="text-scale-ui mt-1 whitespace-nowrap font-medium text-theme">{{ $product['limit_range'] }}</p>
        </div>
        <div class="min-w-0 flex-1 text-center">
          <p class="text-scale-micro text-theme-secondary">{{ __('pages/product-detail.yield_rate') }}</p>
          <p class="text-scale-ui mt-1 whitespace-nowrap font-medium text-theme">{{ $product['rate_range'] }}</p>
        </div>
        <div class="min-w-0 flex-1 text-right">
          <p class="text-scale-micro text-theme-secondary">{{ __('pages/product-detail.cycle') }}</p>
          <p class="text-scale-ui mt-1 whitespace-nowrap font-medium text-theme">{{ $product['cycle_label'] }}</p>
        </div>
      </div>

      <div class="mt-3.5 rounded-2xl border border-theme bg-theme-secondary/70 px-2 py-1.5">
        <div class="flex flex-nowrap items-center gap-2 overflow-x-auto overflow-y-hidden whitespace-nowrap pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
          @foreach ($product['symbol_icon_paths'] as $iconPath)
            <span class="flex h-[clamp(1.5rem,5.2vw,1.9rem)] w-[clamp(1.5rem,5.2vw,1.9rem)] shrink-0 items-center justify-center overflow-hidden rounded-full border border-theme bg-theme-card text-theme-secondary">
              <img src="{{ $iconPath }}" alt="" class="h-[clamp(0.95rem,3.4vw,1.2rem)] w-[clamp(0.95rem,3.4vw,1.2rem)] object-contain">
            </span>
          @endforeach
        </div>
      </div>

    </section>

    @unless($isGuest)
      <section class="mt-6 rounded-2xl border border-theme bg-theme-card p-6">
        <h2 class="text-scale-body font-semibold text-theme">{{ $product['trade_mode'] === 'reserve' ? __('pages/product-detail.reserve_heading') : __('pages/product-detail.buy_heading') }}</h2>
        @if ($product['trade_mode'] === 'reserve')
          <form method="POST" action="/products/{{ $product['id'] }}/reservations" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf
            <div class="sm:w-48">
              <label class="mb-1 block text-scale-micro text-theme-secondary">{{ __('pages/product-detail.reserve_amount_usdt') }}</label>
              <input type="number" min="0.01" step="0.01" name="amount" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-scale-body text-theme" required>
            </div>
            <button class="text-scale-ui h-[clamp(1.9rem,7vw,2.2rem)] min-w-[clamp(7rem,42vw,9rem)] w-auto self-center whitespace-nowrap rounded-lg bg-[rgb(var(--theme-primary))] px-[clamp(0.6rem,2.5vw,0.9rem)] font-semibold text-theme-on-primary mx-auto sm:min-w-[clamp(7.5rem,20vw,10rem)] sm:self-auto sm:mx-0">
              {{ __('pages/product-detail.preorder_now') }}
            </button>
          </form>
        @else
          <p class="mt-3 text-scale-body text-theme-secondary">{{ __('pages/product-detail.current_balance', ['balance' => $balance]) }}</p>

          <form method="POST" action="/positions/purchase" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product['id'] }}">
            <div class="sm:w-48">
              <label class="mb-1 block text-scale-micro text-theme-secondary">{{ __('pages/product-detail.purchase_amount_usdt') }}</label>
              <input type="number" min="0.01" step="0.01" name="amount" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-scale-body text-theme" required>
            </div>
            <button class="text-scale-ui h-[clamp(1.9rem,7vw,2.2rem)] min-w-[clamp(7rem,42vw,9rem)] w-auto self-center whitespace-nowrap rounded-lg bg-[rgb(var(--theme-primary))] px-[clamp(0.6rem,2.5vw,0.9rem)] font-semibold text-theme-on-primary mx-auto sm:min-w-[clamp(7.5rem,20vw,10rem)] sm:self-auto sm:mx-0">
              {{ __('pages/product-detail.buy_now') }}
            </button>
          </form>
        @endif
        @error('amount')
          <p class="mt-3 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
        @enderror
        @error('product')
          <p class="mt-3 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
        @enderror

        @if (!empty($product['description']))
          <div class="mt-6">
            <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/product-detail.intro_title') }}</h2>
            <p class="mt-2 whitespace-pre-line text-scale-body leading-6 text-theme-secondary">{{ $product['description'] }}</p>
          </div>
        @endif
      </section>
    @endunless

    @if ($isGuest && !empty($product['description']))
      <section class="mt-6">
        <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/product-detail.intro_title') }}</h2>
        <p class="mt-2 whitespace-pre-line text-scale-body leading-6 text-theme-secondary">{{ $product['description'] }}</p>
      </section>
    @endif
  </main>

  <x-nav.mobile />
</body>
</html>
