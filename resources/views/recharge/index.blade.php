<!doctype html>
<html lang="{{ __('pages/recharge.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/recharge.meta_title') }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  @php
    $selectedAssetCode = old('asset_code', $defaultAssetCode);
    $selectedAsset = $assets[$selectedAssetCode] ?? (count($assets) > 0 ? reset($assets) : null);
    $selectedAssetCode = $selectedAsset['code'] ?? $selectedAssetCode;
    $localeQuery = 'locale='.urlencode(app()->getLocale());
  @endphp

  <x-nav.top />

  @php
    $mode = request()->query('mode', 'receive');
    $mode = in_array($mode, ['receive', 'send', 'convert'], true) ? $mode : 'receive';
  @endphp

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-8 md:pb-10">
    <x-home.hero :payment-config="[]" :payment-assets="[]" :is-guest="$isGuest" :show-title="false" :show-subtitle="false" />

    <section class="rounded-3xl border border-[rgb(var(--theme-primary))]/20 bg-gradient-to-br from-[rgb(var(--theme-primary))]/10 to-[rgb(var(--theme-accent))]/10 p-6 shadow-xl shadow-[rgb(var(--theme-primary))]/10">
      <div class="rounded-2xl border border-theme bg-theme-card p-3" id="fund-mode-tabs" data-initial-mode="{{ $mode }}">
        <div class="grid grid-cols-3 gap-2">
          <button type="button" data-fund-mode-button="receive" class="rounded-lg border px-3 py-2 text-scale-body font-semibold transition">{{ __('pages/recharge.tabs.receive') }}</button>
          <button type="button" data-fund-mode-button="send" class="rounded-lg border px-3 py-2 text-scale-body font-semibold transition">{{ __('pages/recharge.tabs.send') }}</button>
          <button type="button" data-fund-mode-button="convert" class="rounded-lg border px-3 py-2 text-scale-body font-semibold transition">{{ __('pages/recharge.tabs.convert') }}</button>
        </div>
      </div>

      <div data-fund-mode-panel="receive" class="{{ $mode === 'receive' ? '' : 'hidden' }}">
        @if (count($assets) === 0)
          <div class="mt-6 rounded-xl border border-[rgb(var(--theme-rose))]/40 bg-[rgb(var(--theme-rose))]/10 p-3 text-scale-body text-theme">
            {{ __('pages/recharge.receive.config_missing') }}
          </div>
        @else
          <div class="mt-6 rounded-2xl border border-theme bg-theme-card p-4">
            <p class="text-scale-body text-theme-secondary">{{ __('pages/recharge.receive.currency_type') }}</p>
            <div class="mt-3 flex flex-wrap gap-2" id="asset-selector" role="tablist" aria-label="{{ __('pages/recharge.receive.asset_selector_aria') }}">
              @foreach ($assets as $asset)
                @php $assetCode = $asset['code'] ?? ''; @endphp
                <button
                  type="button"
                  data-asset-code="{{ $assetCode }}"
                  class="rounded-lg border px-3 py-1.5 text-scale-body font-medium transition {{ $assetCode === $selectedAssetCode ? 'border-[rgb(var(--theme-primary))] bg-[rgb(var(--theme-primary))]/15 text-[rgb(var(--theme-primary))]' : 'border-theme text-theme-secondary hover:border-[rgb(var(--theme-primary))]/40 hover:text-theme' }}"
                >
                  {{ $assetCode }}
                </button>
              @endforeach
            </div>
            <p class="mt-3 text-scale-body text-theme-secondary">
              {{ __('pages/recharge.receive.network_label') }}<span id="asset-selector-network" class="font-medium text-theme">{{ __('pages/recharge.receive.network_pattern', ['code' => $selectedAsset['code'] ?? '--', 'network' => $selectedAsset['network'] ?? '--']) }}</span>
            </p>
          </div>

          <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-theme bg-theme-card p-4">
              <dl class="space-y-3 text-scale-body">
                <div>
                  <dt class="text-theme-secondary">{{ __('pages/recharge.receive.address_label') }}</dt>
                  <dd class="mt-1 break-all rounded-lg border border-theme bg-theme-secondary/70 p-2 text-theme" id="wallet-address">{{ $selectedAsset['address'] ?? '--' }}</dd>
                </div>
              </dl>

              <button id="copy-wallet-address" class="mt-4 rounded-lg border border-[rgb(var(--theme-primary))]/40 px-4 py-2 text-scale-body text-[rgb(var(--theme-primary))] hover:bg-[rgb(var(--theme-primary))]/10">
                {{ __('pages/recharge.receive.copy_address') }}
              </button>
              <p id="copy-feedback" class="mt-2 hidden text-scale-micro text-[rgb(var(--theme-primary))]">{{ __('pages/recharge.receive.copied') }}</p>
            </div>
          </div>
        @endif

        @if (session('success'))
          <div class="mt-6 rounded-xl border border-[rgb(var(--theme-primary))]/30 bg-[rgb(var(--theme-primary))]/10 p-3 text-scale-body text-[rgb(var(--theme-primary))]">
            {{ session('success') }}
          </div>
        @endif

        @if ($isGuest)
          <div class="mt-6 rounded-2xl border border-theme bg-theme-card p-5">
            <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/recharge.receive.quick_register_title') }}</h2>
            <p class="mt-2 text-scale-body text-theme-secondary">{{ __('pages/recharge.receive.quick_register_subtitle') }}</p>

            <div class="mt-4 flex flex-wrap items-center gap-3">
              <button id="open-activate-modal" class="rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2 text-scale-body font-semibold text-theme-secondary">{{ __('pages/recharge.receive.activate_account') }}</button>
              <a href="/login?{{ $localeQuery }}" class="text-scale-body text-[rgb(var(--theme-primary))] underline underline-offset-4">{{ __('pages/recharge.receive.go_login') }}</a>
            </div>
          </div>
        @elseif (count($assets) > 0)
          <form method="POST" action="/recharge/requests" enctype="multipart/form-data" class="mt-6 space-y-4 rounded-2xl border border-theme bg-theme-card p-5">
            @csrf

            <input type="hidden" name="asset_code" id="asset_code_input" value="{{ $selectedAssetCode }}">

            <div>
              <label class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge.receive.contact_account_label') }}</label>
              <p class="rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme">{{ auth()->user()?->username ?? '--' }}</p>
            </div>

            <div>
              <label for="payment_amount" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge.receive.payment_amount_label') }}</label>
              <input id="payment_amount" name="payment_amount" type="number" step="0.01" min="0.01" value="{{ old('payment_amount') }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
              @error('payment_amount')
                <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
              @enderror
            </div>

            @error('asset_code')
              <p class="text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
            @enderror

            <div>
              <label for="receipt_image" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge.receive.receipt_image_label') }}</label>
              <div class="flex items-center gap-3 rounded-lg border border-theme bg-theme-secondary px-3 py-2">
                <label for="receipt_image" class="cursor-pointer rounded-md border border-[rgb(var(--theme-primary))]/40 px-3 py-1.5 text-scale-body text-[rgb(var(--theme-primary))] hover:bg-[rgb(var(--theme-primary))]/10">
                  {{ __('pages/recharge.receive.choose_file') }}
                </label>
                <span id="receipt-image-name" class="text-scale-body text-theme-secondary">{{ __('pages/recharge.receive.no_file_selected') }}</span>
              </div>
              <input id="receipt_image" name="receipt_image" type="file" accept="image/*" class="sr-only" required>
              @error('receipt_image')
                <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
              @enderror
            </div>

            <button class="text-scale-ui mx-auto flex h-[clamp(1.9rem,7vw,2.2rem)] w-full items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-[clamp(0.6rem,2.5vw,0.9rem)] font-semibold text-theme-on-primary shadow-lg shadow-[rgb(var(--theme-primary))]/20 transition hover:bg-[rgb(var(--theme-primary))]/90">{{ __('pages/recharge.receive.submit_recharge') }}</button>
          </form>
        @else
          <div class="mt-6 rounded-xl border border-[rgb(var(--theme-rose))]/40 bg-[rgb(var(--theme-rose))]/10 p-3 text-scale-body text-theme">
            {{ __('pages/recharge.receive.no_receiver_available') }}
          </div>
        @endif

        <div class="mt-6">
          <p class="text-scale-body text-theme-secondary">{{ __('pages/recharge.receive.guide_title') }}</p>
          <p class="mt-2 text-scale-body text-theme-secondary">{{ __('pages/recharge.receive.guide_content') }}</p>
        </div>
      </div>

      <div data-fund-mode-panel="send" class="{{ $mode === 'send' ? '' : 'hidden' }}">
        <div class="mt-6 rounded-2xl border border-theme bg-theme-card p-5">
          <h2 class="text-scale-title font-semibold text-theme">{{ __('pages/recharge.send.title') }}</h2>
          <p class="mt-2 text-scale-body text-theme-secondary">{{ __('pages/recharge.send.subtitle') }}</p>

          @if ($isGuest)
            <p class="mt-4 rounded-lg border border-[rgb(var(--theme-rose))]/35 bg-[rgb(var(--theme-rose))]/10 px-3 py-2 text-scale-body text-theme">{{ __('pages/recharge.send.guest_notice') }}</p>
          @else
            <form method="POST" action="/withdrawal-requests" class="mt-4 space-y-4">
              @csrf
              <input type="hidden" name="asset_code" value="USDT">
              <input type="hidden" name="network" value="TRC20">

              <div>
                <label class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge.send.available_balance') }}</label>
                <p class="rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme">{{ number_format((float) (auth()->user()?->balance ?? 0), 2, '.', ',') }}</p>
              </div>
              <div>
                <label for="destination_address" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge.send.destination_address') }}</label>
                <input id="destination_address" name="destination_address" type="text" value="{{ old('destination_address') }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
                @error('destination_address')
                  <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
                @enderror
              </div>
              <div>
                <label for="withdrawal_amount" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge.send.amount') }}</label>
                <input id="withdrawal_amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
                @error('amount')
                  <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
                @enderror
              </div>

              @if (session('success') && $mode === 'send')
                <div class="rounded-xl border border-[rgb(var(--theme-primary))]/30 bg-[rgb(var(--theme-primary))]/10 p-3 text-scale-body text-[rgb(var(--theme-primary))]">
                  {{ session('success') }}
                </div>
              @endif

              <div class="rounded-lg border border-[rgb(var(--theme-primary))]/25 bg-[rgb(var(--theme-primary))]/8 px-3 py-2 text-scale-body text-theme-secondary">
                {{ __('pages/recharge.send.freeze_notice') }}
              </div>
              <button class="text-scale-ui mx-auto flex h-[clamp(1.9rem,7vw,2.2rem)] w-full items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-[clamp(0.6rem,2.5vw,0.9rem)] font-semibold text-theme-on-primary shadow-lg shadow-[rgb(var(--theme-primary))]/20 transition hover:bg-[rgb(var(--theme-primary))]/90">{{ __('pages/recharge.send.submit') }}</button>
            </form>
          @endif
        </div>
      </div>

      <div data-fund-mode-panel="convert" class="{{ $mode === 'convert' ? '' : 'hidden' }}">
        <div class="mt-6 rounded-2xl border border-theme bg-theme-card p-5">
          <h2 class="text-scale-title font-semibold text-theme">{{ __('pages/recharge.convert.title') }}</h2>
          <p class="mt-2 text-scale-body text-theme-secondary">{{ __('pages/recharge.convert.subtitle') }}</p>
          <div class="mt-4 rounded-lg border border-[rgb(var(--theme-primary))]/25 bg-[rgb(var(--theme-primary))]/8 px-3 py-2 text-scale-body text-theme-secondary">
            {{ __('pages/recharge.convert.placeholder') }}
          </div>
        </div>
      </div>
    </section>
  </main>

  <x-nav.mobile />

  @if ($isGuest)
    <x-auth.activate-pin-modal
      modal-id="activate-modal"
      open-button-id="open-activate-modal"
      :invite-code="app(\App\Modules\Referral\Support\InviteCodeResolver::class)->currentForForm(request())"
    />
  @endif

  <script>
    const fundModeTabs = document.getElementById('fund-mode-tabs');
    const fundModeButtons = Array.from(document.querySelectorAll('[data-fund-mode-button]'));
    const fundModePanels = Array.from(document.querySelectorAll('[data-fund-mode-panel]'));

    const setFundMode = (mode) => {
      fundModeButtons.forEach((button) => {
        const isActive = button.getAttribute('data-fund-mode-button') === mode;
        button.classList.toggle('border-[rgb(var(--theme-primary))]', isActive);
        button.classList.toggle('bg-[rgb(var(--theme-primary))]', isActive);
        button.classList.toggle('text-theme-on-primary', isActive);
        button.classList.toggle('border-theme', !isActive);
        button.classList.toggle('bg-theme-secondary/70', !isActive);
        button.classList.toggle('text-theme-secondary', !isActive);
      });

      fundModePanels.forEach((panel) => {
        const isActive = panel.getAttribute('data-fund-mode-panel') === mode;
        panel.classList.toggle('hidden', !isActive);
      });
    };

    const initialFundMode = fundModeTabs?.getAttribute('data-initial-mode') ?? 'receive';
    setFundMode(initialFundMode);

    fundModeTabs?.addEventListener('click', (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) {
        return;
      }

      const button = target.closest('[data-fund-mode-button]');
      const mode = button?.getAttribute('data-fund-mode-button');
      if (!mode) {
        return;
      }

      setFundMode(mode);
    });

    const assets = @json($assets);
    const selector = document.getElementById('asset-selector');
    const assetSelectorNetworkNode = document.getElementById('asset-selector-network');
    const walletAddressNode = document.getElementById('wallet-address');
    const assetCodeInput = document.getElementById('asset_code_input');
    const receiptImageInput = document.getElementById('receipt_image');
    const receiptImageName = document.getElementById('receipt-image-name');

    const copyButton = document.getElementById('copy-wallet-address');
    const feedback = document.getElementById('copy-feedback');
    const rechargeI18n = {
      networkPattern: @json(__('pages/recharge.receive.network_pattern')),
      noFileSelected: @json(__('pages/recharge.receive.no_file_selected')),
    };

    const formatNetworkText = (code, network) => rechargeI18n.networkPattern
      .replace(':code', code ?? '--')
      .replace(':network', network ?? '--');

    const setSelectedAsset = (assetCode) => {
      const asset = assets[assetCode];
      if (!asset) {
        return;
      }

      selector?.querySelectorAll('button[data-asset-code]').forEach((button) => {
        const isActive = button.getAttribute('data-asset-code') === assetCode;
        button.classList.toggle('border-[rgb(var(--theme-primary))]', isActive);
        button.classList.toggle('bg-[rgb(var(--theme-primary))]/15', isActive);
        button.classList.toggle('text-[rgb(var(--theme-primary))]', isActive);
        button.classList.toggle('border-theme', !isActive);
        button.classList.toggle('text-theme-secondary', !isActive);
      });

      if (assetSelectorNetworkNode) assetSelectorNetworkNode.textContent = formatNetworkText(asset.code ?? assetCode, asset.network ?? '--');
      if (walletAddressNode) walletAddressNode.textContent = asset.address ?? '--';
      if (assetCodeInput) assetCodeInput.value = assetCode;
    };

    selector?.addEventListener('click', (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) {
        return;
      }

      const button = target.closest('button[data-asset-code]');
      const assetCode = button?.getAttribute('data-asset-code');
      if (!assetCode) {
        return;
      }

      setSelectedAsset(assetCode);
    });

    copyButton?.addEventListener('click', async () => {
      const address = walletAddressNode?.textContent?.trim() ?? '';
      if (!address) {
        return;
      }

      try {
        await navigator.clipboard.writeText(address);
        feedback?.classList.remove('hidden');
      } catch (error) {
        console.error('copy failed', error);
      }
    });

    receiptImageInput?.addEventListener('change', (event) => {
      const input = event.target;
      if (!(input instanceof HTMLInputElement)) {
        return;
      }

      const fileName = input.files?.[0]?.name ?? rechargeI18n.noFileSelected;
      if (receiptImageName) {
        receiptImageName.textContent = fileName;
      }
    });
  </script>
</body>
</html>
