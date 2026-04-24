<!doctype html>
<html lang="{{ __('pages/product-detail.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ $product['name'] }} | {{ __('pages/product-detail.meta_title_suffix') }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  @php
    $localeQuery = 'locale='.urlencode(app()->getLocale());
  @endphp
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
        @if (session('success'))
          <div class="mt-3 rounded-xl border border-[rgb(var(--theme-primary))]/30 bg-[rgb(var(--theme-primary))]/10 p-3 text-scale-body text-[rgb(var(--theme-primary))]">
            {{ session('success') }}
          </div>
        @endif
        @if ($product['trade_mode'] === 'reserve')
          <form method="POST" action="/products/{{ $product['id'] }}/reservations" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf
            <div class="sm:w-48">
              <label class="mb-1 block text-scale-micro text-theme-secondary">{{ __('pages/product-detail.reserve_amount_usdt') }}</label>
              <div class="relative">
                <input
                  type="number"
                  min="0.01"
                  step="0.01"
                  name="amount"
                  value="{{ old('amount') }}"
                  @if(!empty($product['limit_max_amount'])) max="{{ $product['limit_max_amount'] }}" @endif
                  data-max-amount-target
                  class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 pr-16 text-scale-body text-theme"
                  required
                >
                @if(!empty($product['limit_max_amount']))
                  <button
                    type="button"
                    data-max-amount-trigger
                    data-max-amount="{{ $product['limit_max_amount'] }}"
                    class="absolute right-2 top-1/2 inline-flex h-8 -translate-y-1/2 items-center rounded-md border border-[rgb(var(--theme-primary))]/50 px-2.5 text-scale-micro font-semibold leading-none text-[rgb(var(--theme-primary))] transition hover:bg-[rgb(var(--theme-primary))]/10"
                  >
                    {{ __('pages/product-detail.max_button') }}
                  </button>
                @endif
              </div>
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
              <div class="relative">
                <input
                  type="number"
                  min="0.01"
                  step="0.01"
                  name="amount"
                  value="{{ old('amount') }}"
                  @if(!empty($product['limit_max_amount'])) max="{{ $product['limit_max_amount'] }}" @endif
                  data-max-amount-target
                  class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 pr-16 text-scale-body text-theme"
                  required
                >
                @if(!empty($product['limit_max_amount']))
                  <button
                    type="button"
                    data-max-amount-trigger
                    data-max-amount="{{ $product['limit_max_amount'] }}"
                    class="absolute right-2 top-1/2 inline-flex h-8 -translate-y-1/2 items-center rounded-md border border-[rgb(var(--theme-primary))]/50 px-2.5 text-scale-micro font-semibold leading-none text-[rgb(var(--theme-primary))] transition hover:bg-[rgb(var(--theme-primary))]/10"
                  >
                    {{ __('pages/product-detail.max_button') }}
                  </button>
                @endif
              </div>
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

      @if (session('show_insufficient_balance_prompt'))
        <dialog id="insufficient-balance-prompt" class="theme-modal theme-pin-modal">
          <div class="p-5 md:p-6">
            <p class="text-scale-body font-semibold text-theme">{{ __('pages/product-detail.insufficient_balance_prompt_title') }}</p>

            <div class="mt-5 flex flex-wrap gap-3">
              <a href="/recharge?{{ $localeQuery }}" class="text-scale-ui inline-flex h-[clamp(1.9rem,7vw,2.2rem)] items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2 font-semibold text-theme-on-primary">{{ __('pages/product-detail.insufficient_balance_prompt_confirm') }}</a>
              <button id="insufficient-balance-prompt-cancel" type="button" class="text-scale-ui inline-flex h-[clamp(1.9rem,7vw,2.2rem)] items-center justify-center rounded-lg border border-theme bg-theme-secondary px-4 py-2 font-semibold text-theme">{{ __('pages/product-detail.insufficient_balance_prompt_cancel') }}</button>
            </div>
          </div>
        </dialog>
      @endif
    @endunless

    @if ($isGuest && !empty($product['description']))
      <section class="mt-6">
        <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/product-detail.intro_title') }}</h2>
        <p class="mt-2 whitespace-pre-line text-scale-body leading-6 text-theme-secondary">{{ $product['description'] }}</p>
      </section>
    @endif
  </main>

  <x-nav.mobile />

  <script>
    (() => {
      const promptModal = document.getElementById('insufficient-balance-prompt');
      const cancelButton = document.getElementById('insufficient-balance-prompt-cancel');
      if (!promptModal || typeof promptModal.showModal !== 'function') {
        return;
      }
      promptModal.showModal();
      cancelButton?.addEventListener('click', () => promptModal.close());
    })();

    document.querySelectorAll('[data-max-amount-trigger]').forEach((button) => {
      button.addEventListener('click', () => {
        const input = button.closest('.sm\\:w-48')?.querySelector('[data-max-amount-target]');
        if (!input) return;
        input.value = button.dataset.maxAmount ?? '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });
    });
  </script>
</body>
</html>
