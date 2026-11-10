<x-ui.metric-split-card :use-split-layout="false" wrapper-class="home-data-panel">
  <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/me.account.title') }}</h2>

  <div class="mt-4 rounded-xl border border-theme bg-theme-secondary/20 p-4">
    <div class="grid grid-cols-[minmax(0,1fr)_7rem] items-center gap-x-3 gap-y-1">
      <p class="text-scale-micro text-theme-secondary">{{ $profile['label'] }}</p>
      @if (($profile['user_level'] ?? null) !== null)
        <a href="#" aria-label="Level {{ $profile['user_level'] }}"
           class="inline-block h-6 w-full justify-self-center rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 bg-[image:var(--badge-on-dark)] bg-contain bg-center bg-no-repeat [[data-theme=business]_&]:bg-[image:var(--badge-on-light)]"
           style="--badge-on-dark: url('{{ asset('images/user-level/on-dark-'.$profile['user_level'].'.webp') }}'); --badge-on-light: url('{{ asset('images/user-level/on-light-'.$profile['user_level'].'.webp') }}')"></a>
      @else
        <span aria-hidden="true"></span>
      @endif
      <p class="min-w-0 whitespace-nowrap text-[clamp(0.78rem,3.1vw,0.96rem)] font-semibold tracking-[0.02em] text-theme">{{ $profile['id'] }}</p>
      <button type="button" id="copy-account-button" data-copy-account-button data-copy-text="{{ $profile['id'] }}" data-copy-success-label="{{ __('pages/me.account.copied') }}" class="justify-self-center rounded-xl border border-theme bg-theme-secondary px-2.5 py-1 text-[clamp(0.68rem,2.8vw,0.8rem)] font-semibold text-theme transition hover:border-[rgb(var(--theme-primary))] hover:text-[rgb(var(--theme-primary))]">{{ __('pages/me.account.copy_account') }}</button>
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

  @if (! $isGuest)
    <div @class([
      'mt-4 rounded-xl p-4',
      'border border-[rgb(var(--theme-primary))]/35 bg-[rgb(var(--theme-primary))]/10' => ! $hasMnemonic,
      'border border-theme bg-theme-secondary/20' => $hasMnemonic,
    ])>
      <h3 class="text-scale-body font-semibold text-theme">{{ __('pages/me.account.mnemonic_title') }}</h3>
      @if (! $hasMnemonic)
        <p class="mt-1 text-scale-body font-semibold text-theme">{{ __('pages/me.account.mnemonic_setup_notice') }}</p>
      @endif
      <a href="/me/mnemonic" class="mt-3 inline-flex h-[clamp(1.9rem,7vw,2.2rem)] items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2 text-scale-ui font-semibold text-theme-on-primary">{{ __('pages/me.account.mnemonic_manage') }}</a>
    </div>
  @endif
</x-ui.metric-split-card>
