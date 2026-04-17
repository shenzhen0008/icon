<!doctype html>
<html lang="{{ __('pages/recharge-bank.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/recharge-bank.meta_title') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-8 md:pb-10">
    <x-home.hero :payment-config="[]" :payment-assets="[]" :is-guest="false" :show-title="false" :show-subtitle="false" />

    <section class="rounded-3xl border border-[rgb(var(--theme-primary))]/20 bg-gradient-to-br from-[rgb(var(--theme-primary))]/10 to-[rgb(var(--theme-accent))]/10 p-6 shadow-xl shadow-[rgb(var(--theme-primary))]/10">
      <div class="rounded-2xl border border-theme bg-theme-card p-3" id="bank-mode-tabs" data-initial-mode="{{ $mode }}">
        <div class="grid grid-cols-2 gap-2">
          <button type="button" data-bank-mode-button="receive" class="rounded-lg border px-3 py-2 text-scale-body font-semibold transition">{{ __('pages/recharge-bank.tabs.receive') }}</button>
          <button type="button" data-bank-mode-button="send" class="rounded-lg border px-3 py-2 text-scale-body font-semibold transition">{{ __('pages/recharge-bank.tabs.send') }}</button>
        </div>
      </div>

      @if (session('success'))
        <div class="mt-4 rounded-xl border border-[rgb(var(--theme-primary))]/30 bg-[rgb(var(--theme-primary))]/10 p-3 text-scale-body text-[rgb(var(--theme-primary))]">
          {{ session('success') }}
        </div>
      @endif

      <div data-bank-mode-panel="receive" class="{{ $mode === 'receive' ? '' : 'hidden' }}">
        <div class="mt-6 rounded-2xl border border-theme bg-theme-card p-5">
          <h2 class="text-scale-title font-semibold text-theme">{{ __('pages/recharge-bank.receive.title') }}</h2>
          <p class="mt-2 text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.receive.steps') }}</p>

          @php
            $selectedReceiver = $selectedReceiverKey !== '' ? ($receivers[$selectedReceiverKey] ?? null) : null;
            $receiverBankName = is_array($selectedReceiver) ? (string) ($selectedReceiver['bank_name'] ?? '') : '';
            $receiverAccountName = is_array($selectedReceiver) ? (string) ($selectedReceiver['account_name'] ?? '') : '';
            $receiverCardNumber = is_array($selectedReceiver) ? (string) ($selectedReceiver['card_number'] ?? '') : '';
            $receiverBranchName = is_array($selectedReceiver) ? (string) ($selectedReceiver['branch_name'] ?? '') : '';
          @endphp

          @if ($selectedReceiver === null)
            <div class="mt-4 rounded-lg border border-[rgb(var(--theme-rose))]/35 bg-[rgb(var(--theme-rose))]/10 px-3 py-2 text-scale-body text-theme">
              {{ __('pages/recharge-bank.receive.receiver_unavailable') }}
            </div>
          @else
            <div class="mt-4">
              <p class="text-scale-body text-theme-secondary">收款银行</p>
              <div id="bank-receiver-selector" class="mt-2 flex flex-wrap gap-2" role="tablist" aria-label="Bank receiver selector">
                @foreach ($receivers as $receiver)
                  @php
                    $receiverKey = (string) ($receiver['key'] ?? '');
                    $receiverCode = (string) ($receiver['code'] ?? strtoupper($receiverKey));
                  @endphp
                  <button
                    type="button"
                    data-bank-receiver-key="{{ $receiverKey }}"
                    class="rounded-lg border px-3 py-1.5 text-scale-body font-medium transition {{ $receiverKey === $selectedReceiverKey ? 'border-[rgb(var(--theme-primary))] bg-[rgb(var(--theme-primary))]/15 text-[rgb(var(--theme-primary))]' : 'border-theme text-theme-secondary hover:border-[rgb(var(--theme-primary))]/40 hover:text-theme' }}"
                  >
                    {{ $receiverCode }}
                  </button>
                @endforeach
              </div>
              @error('receiver_key')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <dl class="mt-4 grid gap-3 md:grid-cols-2">
              <div class="rounded-lg border border-theme bg-theme-secondary/70 p-3">
                <dt class="text-scale-micro text-theme-secondary">{{ __('pages/recharge-bank.receive.receiver_bank_name') }}</dt>
                <dd class="mt-1 text-scale-body text-theme" id="receiver-bank-name">{{ $receiverBankName }}</dd>
              </div>
              <div class="rounded-lg border border-theme bg-theme-secondary/70 p-3">
                <dt class="text-scale-micro text-theme-secondary">{{ __('pages/recharge-bank.receive.receiver_account_name') }}</dt>
                <dd class="mt-1 flex items-center justify-between gap-2 text-scale-body text-theme">
                  <span id="receiver-account-name">{{ $receiverAccountName }}</span>
                  <button type="button" data-copy-bank-field="receiver_account_name" data-copy-text="{{ $receiverAccountName }}" class="rounded-md border border-[rgb(var(--theme-primary))]/40 px-2 py-1 text-[rgb(var(--theme-primary))]">{{ __('pages/recharge-bank.receive.copy') }}</button>
                </dd>
              </div>
              <div class="rounded-lg border border-theme bg-theme-secondary/70 p-3 md:col-span-2">
                <dt class="text-scale-micro text-theme-secondary">{{ __('pages/recharge-bank.receive.receiver_card_number') }}</dt>
                <dd class="mt-1 flex items-center justify-between gap-2 text-scale-body text-theme">
                  <span id="receiver-card-number">{{ $receiverCardNumber }}</span>
                  <button type="button" data-copy-bank-field="receiver_card_number" data-copy-text="{{ $receiverCardNumber }}" class="rounded-md border border-[rgb(var(--theme-primary))]/40 px-2 py-1 text-[rgb(var(--theme-primary))]">{{ __('pages/recharge-bank.receive.copy') }}</button>
                </dd>
              </div>
              <div class="rounded-lg border border-theme bg-theme-secondary/70 p-3 md:col-span-2">
                <dt class="text-scale-micro text-theme-secondary">{{ __('pages/recharge-bank.receive.receiver_branch_name') }}</dt>
                <dd class="mt-1 text-scale-body text-theme" id="receiver-branch-name">{{ $receiverBranchName }}</dd>
              </div>
            </dl>

            <form method="POST" action="/recharge/bank/requests" enctype="multipart/form-data" class="mt-6 space-y-4">
              @csrf
              <input type="hidden" name="receiver_key" id="receiver_key_input" value="{{ $selectedReceiverKey }}">
              <div>
                <label class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.receive.contact_account') }}</label>
                <p class="rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme">{{ auth()->user()?->username ?? '--' }}</p>
              </div>
              <div>
                <label for="payment_amount" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.receive.payment_amount') }}</label>
                <input id="payment_amount" name="payment_amount" type="number" step="0.01" min="0.01" value="{{ old('payment_amount') }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
                @error('payment_amount')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
              </div>
              <div>
                <label for="receipt_image" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.receive.receipt_image') }}</label>
                <div class="flex items-center gap-3 rounded-lg border border-theme bg-theme-secondary px-3 py-2">
                  <label for="receipt_image" class="cursor-pointer rounded-md border border-[rgb(var(--theme-primary))]/40 px-3 py-1.5 text-scale-body text-[rgb(var(--theme-primary))] hover:bg-[rgb(var(--theme-primary))]/10">
                    {{ __('pages/recharge-bank.receive.choose_file') }}
                  </label>
                  <span id="bank-receipt-image-name" class="text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.receive.no_file_selected') }}</span>
                </div>
                <input id="receipt_image" name="receipt_image" type="file" accept="image/*" class="sr-only" required>
                @error('receipt_image')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
              </div>
              <div>
                <label for="user_note" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.receive.user_note') }}</label>
                <textarea id="user_note" name="user_note" rows="3" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme">{{ old('user_note') }}</textarea>
                @error('user_note')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
                @error('receiver')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
                @error('bank_card')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
              </div>
              <button class="text-scale-ui mx-auto flex h-10 w-full items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-4 font-semibold text-theme-on-primary transition hover:opacity-90">{{ __('pages/recharge-bank.receive.submit') }}</button>
            </form>
          @endif
        </div>
      </div>

      <div data-bank-mode-panel="send" class="{{ $mode === 'send' ? '' : 'hidden' }}">
        <div class="mt-6 rounded-2xl border border-theme bg-theme-card p-5">
          <h2 class="text-scale-title font-semibold text-theme">{{ __('pages/recharge-bank.send.title') }}</h2>
          <form method="POST" action="/withdrawal-requests" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="asset_code" value="BANK">
            <input type="hidden" name="network" value="BANK_CARD">

            <div>
              <label class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.send.available_balance') }}</label>
              <p class="rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme">{{ number_format($balance, 2, '.', ',') }}</p>
            </div>

            <div>
              <label for="bank_name" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.send.bank_name') }}</label>
              <input id="bank_name" name="bank_name" type="text" value="{{ old('bank_name') }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
              @error('bank_name')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <div>
              <label for="account_name" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.send.account_name') }}</label>
              <input id="account_name" name="account_name" type="text" value="{{ old('account_name') }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
              @error('account_name')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <div>
              <label for="card_number" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.send.card_number') }}</label>
              <input id="card_number" name="card_number" inputmode="numeric" value="{{ old('card_number') }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
              @error('card_number')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <div>
              <label for="branch_name" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.send.branch_name') }}</label>
              <input id="branch_name" name="branch_name" type="text" value="{{ old('branch_name') }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme">
              @error('branch_name')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <div>
              <label for="reserved_phone" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.send.reserved_phone') }}</label>
              <input id="reserved_phone" name="reserved_phone" type="text" value="{{ old('reserved_phone') }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme">
              @error('reserved_phone')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <div>
              <label for="bank_withdrawal_amount" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.send.withdrawal_amount') }}</label>
              <input id="bank_withdrawal_amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
              @error('amount')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <div>
              <label for="bank_withdrawal_note" class="mb-1 block text-scale-body text-theme-secondary">{{ __('pages/recharge-bank.send.note') }}</label>
              <textarea id="bank_withdrawal_note" name="bank_note" rows="3" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme">{{ old('bank_note') }}</textarea>
              @error('bank_note')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <div class="rounded-lg border border-[rgb(var(--theme-primary))]/25 bg-[rgb(var(--theme-primary))]/8 px-3 py-2 text-scale-body text-theme-secondary">
              {{ __('pages/recharge-bank.send.freeze_notice') }}
            </div>

            <button class="text-scale-ui mx-auto flex h-10 w-full items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-4 font-semibold text-theme-on-primary transition hover:opacity-90">{{ __('pages/recharge-bank.send.submit') }}</button>
          </form>
        </div>
      </div>
    </section>
  </main>

  <x-nav.mobile />

  <script>
    const bankModeTabs = document.getElementById('bank-mode-tabs');
    const bankModeButtons = Array.from(document.querySelectorAll('[data-bank-mode-button]'));
    const bankModePanels = Array.from(document.querySelectorAll('[data-bank-mode-panel]'));

    const setBankMode = (mode) => {
      bankModeButtons.forEach((button) => {
        const isActive = button.getAttribute('data-bank-mode-button') === mode;
        button.classList.toggle('border-[rgb(var(--theme-primary))]', isActive);
        button.classList.toggle('bg-[rgb(var(--theme-primary))]', isActive);
        button.classList.toggle('text-theme-on-primary', isActive);
        button.classList.toggle('border-theme', !isActive);
        button.classList.toggle('bg-theme-secondary/70', !isActive);
        button.classList.toggle('text-theme-secondary', !isActive);
      });

      bankModePanels.forEach((panel) => {
        panel.classList.toggle('hidden', panel.getAttribute('data-bank-mode-panel') !== mode);
      });
    };

    setBankMode(bankModeTabs?.getAttribute('data-initial-mode') ?? 'receive');

    bankModeTabs?.addEventListener('click', (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;

      const button = target.closest('[data-bank-mode-button]');
      const mode = button?.getAttribute('data-bank-mode-button');
      if (!mode) return;
      setBankMode(mode);
    });

    const receivers = @json($receivers);
    const receiverSelector = document.getElementById('bank-receiver-selector');
    const receiverKeyInput = document.getElementById('receiver_key_input');
    const receiverBankNameNode = document.getElementById('receiver-bank-name');
    const receiverAccountNameNode = document.getElementById('receiver-account-name');
    const receiverCardNumberNode = document.getElementById('receiver-card-number');
    const receiverBranchNameNode = document.getElementById('receiver-branch-name');

    const setSelectedReceiver = (key) => {
      const receiver = receivers[key];
      if (!receiver) return;

      if (receiverKeyInput) receiverKeyInput.value = key;
      if (receiverBankNameNode) receiverBankNameNode.textContent = receiver.bank_name ?? '';
      if (receiverAccountNameNode) receiverAccountNameNode.textContent = receiver.account_name ?? '';
      if (receiverCardNumberNode) receiverCardNumberNode.textContent = receiver.card_number ?? '';
      if (receiverBranchNameNode) receiverBranchNameNode.textContent = receiver.branch_name ?? '';

      document.querySelectorAll('[data-copy-bank-field="receiver_account_name"]').forEach((node) => {
        node.setAttribute('data-copy-text', receiver.account_name ?? '');
      });
      document.querySelectorAll('[data-copy-bank-field="receiver_card_number"]').forEach((node) => {
        node.setAttribute('data-copy-text', receiver.card_number ?? '');
      });

      document.querySelectorAll('[data-bank-receiver-key]').forEach((button) => {
        const isActive = button.getAttribute('data-bank-receiver-key') === key;
        button.classList.toggle('border-[rgb(var(--theme-primary))]', isActive);
        button.classList.toggle('bg-[rgb(var(--theme-primary))]/15', isActive);
        button.classList.toggle('text-[rgb(var(--theme-primary))]', isActive);
        button.classList.toggle('border-theme', !isActive);
        button.classList.toggle('text-theme-secondary', !isActive);
      });
    };

    receiverSelector?.addEventListener('click', (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;
      const button = target.closest('[data-bank-receiver-key]');
      const key = button?.getAttribute('data-bank-receiver-key');
      if (!key) return;
      setSelectedReceiver(key);
    });

    const receiptInput = document.getElementById('receipt_image');
    const receiptName = document.getElementById('bank-receipt-image-name');
    receiptInput?.addEventListener('change', () => {
      const fileName = receiptInput.files?.[0]?.name ?? @json(__('pages/recharge-bank.receive.no_file_selected'));
      if (receiptName) receiptName.textContent = fileName;
    });

    document.querySelectorAll('[data-copy-bank-field]').forEach((node) => {
      node.addEventListener('click', async () => {
        if (!(node instanceof HTMLElement)) return;
        const text = node.getAttribute('data-copy-text') ?? '';
        if (!text) return;

        await navigator.clipboard.writeText(text);
        node.textContent = @json(__('pages/recharge-bank.receive.copied'));
        setTimeout(() => {
          node.textContent = @json(__('pages/recharge-bank.receive.copy'));
        }, 1200);
      });
    });
  </script>
</body>
</html>
