<x-ui.metric-split-card :use-split-layout="false" wrapper-class="home-data-panel">
  <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/me.account.title') }}</h2>

  <div class="mt-4 rounded-xl border border-theme bg-theme-secondary/20 p-4">
    <p class="text-scale-micro text-theme-secondary">{{ $profile['label'] }}</p>
    <div class="mt-1 flex flex-nowrap items-center justify-between gap-3">
      <p class="min-w-0 flex-1 truncate whitespace-nowrap text-scale-title font-semibold tracking-wider text-theme" title="{{ $profile['id'] }}">{{ $profile['id'] }}</p>
      <button type="button" id="copy-account-button" data-copy-text="{{ $profile['id'] }}" class="shrink-0 rounded-2xl border border-theme bg-theme-secondary px-3 py-1.5 text-scale-body font-semibold text-theme transition hover:border-[rgb(var(--theme-primary))] hover:text-[rgb(var(--theme-primary))]">{{ __('pages/me.account.copy_account') }}</button>
    </div>
  </div>

  <dl class="mt-4 grid grid-cols-3 gap-3 text-scale-body">
    <div class="rounded-lg border border-theme bg-theme-secondary/20 p-3">
      <dt class="text-theme-secondary">{{ __('pages/me.account.status_label') }}</dt>
      <dd class="mt-1 text-theme">{{ $profile['status'] }}</dd>
    </div>
    <div class="rounded-lg border border-theme bg-theme-secondary/20 p-3">
      <dt class="text-theme-secondary">{{ __('pages/me.account.created_at_label') }}</dt>
      <dd class="mt-1 text-theme">{{ $profile['created_at'] }}</dd>
    </div>
    @if (! $isGuest)
      <div class="flex items-center justify-center rounded-xl border border-[rgb(var(--theme-rose))]/20 bg-[rgb(var(--theme-rose))]/10 p-3">
        <form method="POST" action="/logout" class="w-full">
          @csrf
          <button class="text-scale-ui mx-auto flex h-[clamp(1.9rem,7vw,2.2rem)] w-full max-w-[9.5rem] items-center justify-center whitespace-nowrap rounded-lg bg-[rgb(var(--theme-rose))] px-4 py-2 font-semibold text-theme-on-primary">{{ __('pages/me.account.logout') }}</button>
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
        this.textContent = @json(__('pages/me.account.copied'));
        setTimeout(() => {
          this.textContent = originalText;
        }, 1500);
      });
    });
  </script>
</x-ui.metric-split-card>
