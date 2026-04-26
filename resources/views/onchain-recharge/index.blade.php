<!doctype html>
<html lang="{{ __('pages/onchain-recharge.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/onchain-recharge.meta_title', ['app_name' => config('app.name')]) }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  @php
    $selectedAssetCode = old('asset_code', $defaultAssetCode);
    $selectedAsset = $assets[$selectedAssetCode] ?? (count($assets) > 0 ? reset($assets) : null);
    $selectedAssetCode = $selectedAsset['code'] ?? $selectedAssetCode;
    $localeQuery = 'locale='.urlencode(app()->getLocale());
  @endphp

  <main class="mx-auto w-full max-w-4xl px-4 pb-4 pt-8 md:pb-8">
    <section class="rounded-3xl border border-[rgb(var(--theme-primary))]/20 bg-gradient-to-br from-[rgb(var(--theme-primary))]/10 to-[rgb(var(--theme-accent))]/10 p-6 shadow-xl shadow-[rgb(var(--theme-primary))]/10">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h1 class="text-scale-display font-semibold text-theme">{{ __('pages/onchain-recharge.title') }}</h1>
          <p class="mt-2 text-scale-body text-theme-secondary">{{ __('pages/onchain-recharge.intro') }}</p>
        </div>
        <a href="/recharge?{{ $localeQuery }}" class="rounded-lg border border-theme px-3 py-2 text-scale-body text-theme-secondary hover:text-theme">{{ __('pages/onchain-recharge.back_to_recharge') }}</a>
      </div>

      @if (session('success'))
        <div class="mt-6 rounded-xl border border-[rgb(var(--theme-primary))]/30 bg-[rgb(var(--theme-primary))]/10 p-3 text-scale-body text-[rgb(var(--theme-primary))]">
          {{ session('success') }}
        </div>
      @endif

      @if (count($assets) === 0)
        <div class="mt-6 rounded-xl border border-[rgb(var(--theme-rose))]/40 bg-[rgb(var(--theme-rose))]/10 p-3 text-scale-body text-theme">
          {{ __('pages/onchain-recharge.no_channel') }}
        </div>
      @else
        <div class="mt-6 rounded-2xl border border-theme bg-theme-card p-4">
          <p class="text-scale-body font-semibold text-theme">{{ __('pages/onchain-recharge.dev_prefill_params') }}</p>
          <div class="mt-3 grid gap-3 md:grid-cols-2">
            <div>
              <p class="text-scale-micro text-theme-secondary">{{ __('pages/onchain-recharge.chain_id') }}</p>
              <p class="mt-1 rounded-lg border border-theme bg-theme-secondary px-3 py-2 font-mono text-scale-body text-theme">{{ $paymentConfig['chain_id'] }}</p>
            </div>
            <div>
              <p class="text-scale-micro text-theme-secondary">{{ __('pages/onchain-recharge.token_address_usdt') }}</p>
              <p class="mt-1 break-all rounded-lg border border-theme bg-theme-secondary px-3 py-2 font-mono text-scale-body text-theme">{{ $paymentConfig['token_address'] }}</p>
            </div>
            <div>
              <p class="text-scale-micro text-theme-secondary">{{ __('pages/onchain-recharge.default_receiver_address') }}</p>
              <p id="receiver-address-preview" class="mt-1 break-all rounded-lg border border-theme bg-theme-secondary px-3 py-2 font-mono text-scale-body text-theme">{{ $selectedAsset['address'] ?? '' }}</p>
            </div>
          </div>
        </div>

        <form
          method="POST"
          action="/recharge/onchain/requests"
          class="mt-6 space-y-4 rounded-2xl border border-theme bg-theme-card p-5"
          data-onchain-recharge-form
          data-token-address="{{ $paymentConfig['token_address'] }}"
          data-walletconnect-project-id="{{ $paymentConfig['walletconnect_project_id'] }}"
        >
          @csrf

          <div>
            <label class="mb-2 block text-scale-body text-theme-secondary">{{ __('pages/onchain-recharge.select_asset') }}</label>
            <div id="asset-quick-picker" class="grid grid-cols-2 gap-2 md:grid-cols-4">
              @foreach ($assets as $asset)
                <button
                  type="button"
                  data-asset-code="{{ $asset['code'] }}"
                  class="asset-chip rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-left text-scale-body text-theme transition hover:bg-theme-secondary/70"
                >
                  <span class="block font-semibold">{{ $asset['code'] }}</span>
                  <span class="block text-scale-micro text-theme-secondary">{{ $asset['network'] }}</span>
                </button>
              @endforeach
            </div>
          </div>

          <div>
            <label for="asset_code" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/onchain-recharge.asset') }}</label>
            <select id="asset_code" name="asset_code" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
              @foreach ($assets as $asset)
                <option
                  value="{{ $asset['code'] }}"
                  data-address="{{ $asset['address'] }}"
                  data-network="{{ $asset['network'] }}"
                  @selected($selectedAssetCode === $asset['code'])
                >
                  {{ $asset['code'] }} / {{ $asset['network'] }}
                </option>
              @endforeach
            </select>
            @error('asset_code')
              <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
            @enderror
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label for="payment_amount" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/onchain-recharge.payment_amount') }}</label>
              <input id="payment_amount" name="payment_amount" type="number" step="0.01" min="0.01" value="{{ old('payment_amount', $defaultPaymentAmount) }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
              @error('payment_amount')
                <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="chain_id" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/onchain-recharge.chain_id') }}</label>
              <input id="chain_id" name="chain_id" type="text" value="{{ old('chain_id', $defaultChainId) }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
              @error('chain_id')
                <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div>
            <label for="to_address_display" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/onchain-recharge.receiving_address') }}</label>
            <input id="to_address_display" type="text" value="{{ $selectedAsset['address'] ?? '' }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 font-mono text-theme" readonly>
          </div>

          <div>
            <label for="tx_hash" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/onchain-recharge.tx_hash') }}</label>
            <input id="tx_hash" name="tx_hash" type="text" value="{{ old('tx_hash', $defaultTxHash) }}" placeholder="0x..." class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
            @error('tx_hash')
              <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="from_address" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/onchain-recharge.payer_wallet') }}</label>
            <input id="from_address" name="from_address" type="text" value="{{ old('from_address', $defaultFromAddress) }}" placeholder="{{ __('pages/onchain-recharge.payer_wallet_placeholder') }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme">
            <p id="wallet-connect-feedback" class="mt-1 hidden text-scale-micro text-[rgb(var(--theme-primary))]"></p>
            @error('from_address')
              <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
            @enderror
          </div>

          <div class="rounded-xl border border-theme bg-theme-secondary/30 p-3">
            <button type="button" id="pay-direct-btn" class="w-full rounded-lg border border-[rgb(var(--theme-primary))]/40 px-3 py-2 text-scale-body font-semibold text-[rgb(var(--theme-primary))] hover:bg-[rgb(var(--theme-primary))]/10">{{ __('pages/onchain-recharge.pay_button') }}</button>
            <p id="pay-feedback" class="mt-2 hidden text-scale-micro text-[rgb(var(--theme-primary))]"></p>
          </div>

          <div>
            <label for="user_note" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/onchain-recharge.note') }}</label>
            <textarea id="user_note" name="user_note" rows="3" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" placeholder="{{ __('pages/onchain-recharge.note_placeholder') }}">{{ old('user_note') }}</textarea>
            @error('user_note')
              <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
            @enderror
          </div>

          <button class="text-scale-ui mx-auto flex h-[clamp(1.9rem,7vw,2.2rem)] w-full items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-[clamp(0.6rem,2.5vw,0.9rem)] font-semibold text-theme-on-primary shadow-lg shadow-[rgb(var(--theme-primary))]/20 transition hover:bg-[rgb(var(--theme-primary))]/90">{{ __('pages/onchain-recharge.submit') }}</button>
        </form>
      @endif
    </section>
  </main>

  <x-nav.mobile />
</body>
</html>
