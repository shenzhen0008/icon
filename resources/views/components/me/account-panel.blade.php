<section class="rounded-2xl border border-theme bg-theme-card p-5">
  <h2 class="text-base font-semibold text-theme">账号信息</h2>

  <div class="mt-4 rounded-xl border border-theme bg-theme-secondary/20 p-4">
    <p class="text-xs text-theme-secondary">{{ $profile['label'] }}</p>
    <div class="mt-1 flex items-start justify-between gap-3">
      <p class="min-w-0 break-all text-lg font-semibold tracking-wider text-theme">{{ $profile['id'] }}</p>
      <button type="button" id="copy-account-button" data-copy-text="{{ $profile['id'] }}" class="shrink-0 rounded-2xl border border-theme bg-theme-secondary px-3 py-1.5 text-sm font-semibold text-theme transition hover:border-[rgb(var(--theme-primary))] hover:text-[rgb(var(--theme-primary))]">复制账号</button>
    </div>
  </div>

  <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
    <div class="rounded-lg border border-theme bg-theme-secondary/20 p-3">
      <dt class="text-theme-secondary">账号状态</dt>
      <dd class="mt-1 text-theme">{{ $profile['status'] }}</dd>
    </div>
    <div class="rounded-lg border border-theme bg-theme-secondary/20 p-3">
      <dt class="text-theme-secondary">创建时间</dt>
      <dd class="mt-1 text-theme">{{ $profile['created_at'] }}</dd>
    </div>
    @if (! $isGuest)
      <div class="rounded-xl border border-[rgb(var(--theme-rose))]/20 bg-[rgb(var(--theme-rose))]/10 p-3">
        <form method="POST" action="/logout">
          @csrf
          <button class="rounded-lg bg-[rgb(var(--theme-rose))] px-4 py-2 text-sm font-semibold text-theme-secondary">退出登录</button>
        </form>
      </div>
    @endif
  </dl>

  <script>
    document.getElementById('copy-account-button')?.addEventListener('click', function () {
      const copyText = this.dataset.copyText;
      if (!copyText) return;

      navigator.clipboard.writeText(copyText).then(() => {
        const originalText = this.textContent;
        this.textContent = '已复制';
        setTimeout(() => {
          this.textContent = originalText;
        }, 1500);
      });
    });
  </script>
</section>
