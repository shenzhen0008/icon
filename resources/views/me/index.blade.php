<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>我的 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-8 md:pb-10">
    <x-home.hero :payment-config="[]" :payment-assets="[]" :is-guest="$isGuest" :show-title="false" :show-subtitle="false" />

    <div class="space-y-5">
      <x-me.payment-method-panel />
      <x-me.account-panel :profile="$profile" :is-guest="$isGuest" />

      @if ($isGuest)
        <x-me.guest-auth-panel />
      @endif
    </div>

  </main>

  <x-nav.mobile />

  @if ($isGuest)
    <dialog id="activate-modal" class="theme-modal">
      <div class="p-5 md:p-6">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-scale-title font-semibold">设置密码注册</h2>
          <button id="close-activate-modal" class="rounded-lg px-2.5 py-1.5 text-theme-secondary hover:bg-theme-secondary/60">关闭</button>
        </div>

        <form method="POST" action="/register" class="space-y-4">
          @csrf
          <input type="hidden" name="invite_code" value="{{ app(\App\Modules\Referral\Support\InviteCodeResolver::class)->currentForForm(request()) }}">

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

          <button class="w-full rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2.5 font-semibold text-theme-on-primary">确认注册</button>
        </form>
      </div>
    </dialog>

    <script>
      const modal = document.getElementById('activate-modal');
      const openBtn = document.getElementById('open-activate-modal');
      const closeBtn = document.getElementById('close-activate-modal');

      openBtn?.addEventListener('click', () => modal?.showModal());
      closeBtn?.addEventListener('click', () => modal?.close());
      modal?.addEventListener('click', (event) => {
        const rect = modal.getBoundingClientRect();
        const isInside =
          event.clientX >= rect.left &&
          event.clientX <= rect.right &&
          event.clientY >= rect.top &&
          event.clientY <= rect.bottom;

        if (!isInside) modal.close();
      });

      @if ($errors->has('password'))
        modal?.showModal();
      @endif
    </script>
  @endif
</body>
</html>
