<x-ui.metric-split-card :use-split-layout="false" wrapper-class="home-data-panel">
  <h2 class="text-scale-body font-semibold text-theme">快速注册</h2>
  <p class="mt-2 text-scale-body text-theme-secondary">你当前是访客态，设置密码后即可将临时账号升级为正式账号。</p>

  <div class="mt-4 flex flex-wrap items-center gap-3">
    <button id="open-activate-modal" class="text-scale-ui inline-flex h-[clamp(1.9rem,7vw,2.2rem)] items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-[clamp(0.6rem,2.5vw,0.9rem)] font-semibold text-theme-on-primary">设置密码并注册</button>
    <a href="/login" class="text-scale-body text-[rgb(var(--theme-primary))] underline underline-offset-4">已有账号？去登录</a>
  </div>
</x-ui.metric-split-card>
