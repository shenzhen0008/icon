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

  <main class="mx-auto w-full max-w-7xl px-2 pb-28 pt-8 md:pb-10">
    <div class="grid gap-5 lg:grid-cols-3">
      <div class="order-last lg:order-none lg:col-span-1">
        <x-me.account-panel :profile="$profile" :is-guest="$isGuest" />
      </div>

      <div class="order-first space-y-5 lg:order-none lg:col-span-2">
        <x-me.profit-panel :summary="$summary" />
        <x-me.positions-panel :positions="$positions" />

        @if ($isGuest)
          <x-me.guest-auth-panel />
        @endif
      </div>
    </div>

  </main>

  <x-nav.mobile />

  @if ($isGuest)
    <dialog id="activate-modal" class="w-full max-w-md rounded-2xl border border-white/10 bg-slate-900 p-0 text-slate-100 backdrop:bg-black/70">
      <div class="p-6">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-semibold">设置密码注册</h2>
          <button id="close-activate-modal" class="rounded px-2 py-1 text-slate-300 hover:bg-white/10">关闭</button>
        </div>

        <form method="POST" action="/register" class="space-y-4">
          @csrf

          <div>
            <label class="mb-1 block text-sm" for="password">密码</label>
            <input id="password" type="password" name="password" class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2" required>
            @error('password')
              <p class="mt-1 text-sm text-[rgb(var(--theme-rose))]">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="mb-1 block text-sm" for="password_confirmation">确认密码</label>
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

      @if ($errors->has('password'))
        modal?.showModal();
      @endif
    </script>
  @endif
</body>
</html>
