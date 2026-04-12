<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>充值 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  @php
    $selectedAssetCode = old('asset_code', $defaultAssetCode);
    $selectedAsset = $assets[$selectedAssetCode] ?? (count($assets) > 0 ? reset($assets) : null);
    $selectedAssetCode = $selectedAsset['code'] ?? $selectedAssetCode;
  @endphp

  <x-nav.top />

  <main class="mx-auto w-full max-w-7xl px-4 pb-28 pt-8 md:pb-10">
    <section class="rounded-3xl border border-[rgb(var(--theme-primary))]/20 bg-gradient-to-br from-[rgb(var(--theme-primary))]/10 to-[rgb(var(--theme-accent))]/10 p-6 shadow-xl shadow-[rgb(var(--theme-primary))]/10">
      <h1 class="text-scale-display font-semibold text-theme">充值</h1>
      <p class="mt-2 text-scale-body text-theme-secondary">请选择币种后向对应地址付款，完成后上传付款截图，管理员将手动核实并入账。</p>

      @if (count($assets) === 0)
        <div class="mt-6 rounded-xl border border-[rgb(var(--theme-rose))]/40 bg-[rgb(var(--theme-rose))]/10 p-3 text-scale-body text-theme">
          收款配置缺失，请联系管理员。
        </div>
      @else
        <div class="mt-6 rounded-2xl border border-theme bg-theme-card p-4">
          <p class="text-scale-body text-theme-secondary">收款通道</p>
          <div class="mt-3 flex flex-wrap gap-2" id="asset-selector" role="tablist" aria-label="币种选择">
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
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
          <div class="rounded-2xl border border-theme bg-theme-card p-4">
            <p class="text-scale-body text-theme-secondary">当前币种</p>
            <p class="mt-2 text-scale-title font-semibold text-[rgb(var(--theme-primary))]" id="asset-code">{{ $selectedAsset['code'] ?? '--' }}</p>

            <dl class="mt-4 space-y-3 text-scale-body">
              <div>
                <dt class="text-theme-secondary">网络</dt>
                <dd class="mt-1 font-medium text-theme" id="asset-network">{{ $selectedAsset['network'] ?? '--' }}</dd>
              </div>
              <div>
                <dt class="text-theme-secondary">收款地址</dt>
                <dd class="mt-1 break-all rounded-lg border border-theme bg-theme-secondary/70 p-2 text-theme" id="wallet-address">{{ $selectedAsset['address'] ?? '--' }}</dd>
              </div>
            </dl>

            <button id="copy-wallet-address" class="mt-4 rounded-lg border border-[rgb(var(--theme-primary))]/40 px-4 py-2 text-scale-body text-[rgb(var(--theme-primary))] hover:bg-[rgb(var(--theme-primary))]/10">
              复制收款地址
            </button>
            <p id="copy-feedback" class="mt-2 hidden text-scale-micro text-[rgb(var(--theme-primary))]">已复制</p>
          </div>

          <div class="rounded-2xl border border-theme bg-theme-card p-4">
            <p class="text-scale-body text-theme-secondary">操作说明</p>
            <ul class="mt-3 space-y-2 list-disc pl-5 text-scale-body text-theme-secondary">
              <li>请确认付款币种、网络、地址完全一致。</li>
              <li>付款成功后再提交申请，避免截图信息不完整。</li>
              <li>管理员核实后会手动增加余额，请耐心等待。</li>
            </ul>
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
          <h2 class="text-scale-body font-semibold text-theme">快速注册</h2>
          <p class="mt-2 text-scale-body text-theme-secondary">你当前是访客态，设置密码后即可将临时账号升级为正式账号。</p>

          <div class="mt-4 flex flex-wrap items-center gap-3">
            <button id="open-activate-modal" class="rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2 text-scale-body font-semibold text-theme-secondary">设置密码并注册</button>
            <a href="/login" class="text-scale-body text-[rgb(var(--theme-primary))] underline underline-offset-4">已有账号？去登录</a>
          </div>
        </div>
      @elseif (count($assets) > 0)
        <form method="POST" action="/recharge/requests" enctype="multipart/form-data" class="mt-6 space-y-4 rounded-2xl border border-theme bg-theme-card p-5">
          @csrf

          <input type="hidden" name="asset_code" id="asset_code_input" value="{{ $selectedAssetCode }}">

          <div>
            <label class="mb-1 block text-scale-body text-theme-secondary">联系账号（用于人工核对）</label>
            <p class="rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme">{{ auth()->user()?->username ?? '--' }}</p>
          </div>

          <div>
            <label for="payment_amount" class="mb-1 block text-scale-body text-theme-secondary">付款金额</label>
            <input id="payment_amount" name="payment_amount" type="number" step="0.01" min="0.01" value="{{ old('payment_amount') }}" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" required>
            @error('payment_amount')
              <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
            @enderror
          </div>

          @error('asset_code')
            <p class="text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
          @enderror

          <div>
            <label for="receipt_image" class="mb-1 block text-scale-body text-theme-secondary">付款截图</label>
            <div class="flex items-center gap-3 rounded-lg border border-theme bg-theme-secondary px-3 py-2">
              <label for="receipt_image" class="cursor-pointer rounded-md border border-[rgb(var(--theme-primary))]/40 px-3 py-1.5 text-scale-body text-[rgb(var(--theme-primary))] hover:bg-[rgb(var(--theme-primary))]/10">
                选择文件
              </label>
              <span id="receipt-image-name" class="text-scale-body text-theme-secondary">未选择文件</span>
            </div>
            <input id="receipt_image" name="receipt_image" type="file" accept="image/*" class="sr-only" required>
            @error('receipt_image')
              <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="user_note" class="mb-1 block text-scale-body text-theme-secondary">备注（可选）</label>
            <textarea id="user_note" name="user_note" rows="3" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-theme" placeholder="例如：付款时间、末尾交易哈希等">{{ old('user_note') }}</textarea>
            @error('user_note')
              <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
            @enderror
          </div>

          <button class="text-scale-ui mx-auto flex h-[clamp(1.9rem,7vw,2.2rem)] w-full items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-[clamp(0.6rem,2.5vw,0.9rem)] font-semibold text-theme-on-primary shadow-lg shadow-[rgb(var(--theme-primary))]/20 transition hover:bg-[rgb(var(--theme-primary))]/90">提交充值申请</button>
        </form>
      @else
        <div class="mt-6 rounded-xl border border-[rgb(var(--theme-rose))]/40 bg-[rgb(var(--theme-rose))]/10 p-3 text-scale-body text-theme">
          当前暂无可用收款账户，请联系管理员。
        </div>
      @endif
    </section>
  </main>

  <x-nav.mobile />

  @if ($isGuest)
    <dialog id="activate-modal" class="w-full max-w-md rounded-2xl border border-white/10 bg-slate-900 p-0 text-slate-100 backdrop:bg-black/70">
      <div class="p-6">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-scale-title font-semibold">设置密码注册</h2>
          <button id="close-activate-modal" class="rounded px-2 py-1 text-slate-300 hover:bg-white/10">关闭</button>
        </div>

        <form method="POST" action="/register" class="space-y-4">
          @csrf

          <div>
            <label class="mb-1 block text-scale-body" for="password">密码</label>
            <input id="password" type="password" name="password" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2" required>
            @error('password')
              <p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="mb-1 block text-scale-body" for="password_confirmation">确认密码</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2" required>
          </div>

          <button class="w-full rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2.5 font-semibold text-theme-secondary">确认注册</button>
        </form>
      </div>
    </dialog>

    <script>
      const modal = document.getElementById('activate-modal');
      const openBtn = document.getElementById('open-activate-modal');
      const closeBtn = document.getElementById('close-activate-modal');

      openBtn?.addEventListener('click', () => modal?.showModal());
      closeBtn?.addEventListener('click', () => modal?.close());

      @if ($errors->has('password'))
        modal?.showModal();
      @endif
    </script>
  @endif

  <script>
    const assets = @json($assets);
    const selector = document.getElementById('asset-selector');
    const assetCodeNode = document.getElementById('asset-code');
    const assetNetworkNode = document.getElementById('asset-network');
    const walletAddressNode = document.getElementById('wallet-address');
    const assetCodeInput = document.getElementById('asset_code_input');
    const receiptImageInput = document.getElementById('receipt_image');
    const receiptImageName = document.getElementById('receipt-image-name');

    const copyButton = document.getElementById('copy-wallet-address');
    const feedback = document.getElementById('copy-feedback');

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

      if (assetCodeNode) assetCodeNode.textContent = asset.code ?? assetCode;
      if (assetNetworkNode) assetNetworkNode.textContent = asset.network ?? '--';
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

      const fileName = input.files?.[0]?.name ?? '未选择文件';
      if (receiptImageName) {
        receiptImageName.textContent = fileName;
      }
    });
  </script>
</body>
</html>
