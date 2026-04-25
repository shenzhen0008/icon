<!doctype html>
<html lang="{{ __('pages/referral.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/referral.meta_title', ['app_name' => config('app.name')]) }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="relative flex min-h-[calc(var(--app-vh,100dvh)-var(--top-nav-height,4rem)-var(--mobile-nav-height,4rem))] w-full flex-col pt-[12rem] md:mx-auto md:max-w-4xl md:min-h-0 md:px-4 md:pb-10 md:pt-[30rem]">
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 overflow-visible">
      <div class="absolute inset-x-0 top-0 h-[14rem] bg-gradient-to-b from-[#0f47d9] via-[#2b66f6]/95 via-45% to-transparent md:h-[36rem]"></div>
      <img
        src="{{ asset('images/share.png') }}"
        alt=""
        class="relative h-auto w-full object-contain object-top opacity-82 saturate-125"
      >
    </div>

    <section class="relative px-4 pb-24 pt-4 text-white">
      <div class="relative z-10 max-w-[18rem] md:max-w-[20rem]">
        <p class="text-scale-micro font-semibold uppercase tracking-[0.12em] text-white/80">{{ __('pages/referral.hero_tagline') }}</p>
        <h1 class="mt-1.5 whitespace-nowrap text-[clamp(1.72rem,7vw,2.3rem)] font-semibold leading-[1.04]">{{ __('pages/referral.hero_title') }}</h1>
        <p class="mt-2 text-scale-body leading-5 text-white/90">
          {{ __('pages/referral.hero_subtitle') }}
        </p>
      </div>
    </section>

    <section class="relative -mt-[4.6rem] mx-4 flex-1 rounded-[8px] border border-theme bg-theme-card p-4 shadow-[0_14px_30px_rgb(var(--theme-primary))/0.1] md:mx-0 md:flex-none">
      <div class="rounded-[8px] border border-theme bg-theme-secondary/25 p-3">
        <div class="relative flex items-center justify-between gap-3">
          <p class="text-scale-body font-semibold text-theme">{{ __('pages/referral.reward_info') }}</p>
          <button
            type="button"
            id="reward-help-toggle"
            aria-label="{{ __('pages/referral.reward_help_aria') }}"
            aria-expanded="false"
            aria-controls="reward-help-panel"
            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[rgb(var(--theme-primary))]/35 bg-theme-card text-sm font-semibold text-[rgb(var(--theme-primary))] shadow-[0_8px_16px_rgb(var(--theme-primary))/0.12]"
          >
            ?
          </button>
          <div
            id="reward-help-panel"
            class="pointer-events-none absolute right-0 top-full z-10 mt-2 w-[min(22rem,calc(100vw-2rem))] translate-y-1 rounded-[8px] border border-theme bg-theme-card px-4 py-3 text-scale-body leading-6 text-theme-secondary opacity-0 shadow-[0_16px_35px_rgb(var(--theme-primary))/0.16] transition-all duration-200 ease-out"
          >
            <div class="absolute -top-2 right-3 h-4 w-4 rotate-45 border-l border-t border-theme bg-theme-card"></div>
            <div class="relative max-h-[18rem] overflow-y-auto pr-1">
              <p class="font-semibold text-theme">{{ __('pages/referral.help_title') }}</p>
              <p class="mt-2">{{ __('pages/referral.help_p1') }}</p>
              <p class="mt-2">{{ __('pages/referral.help_p2') }}</p>
              <p class="mt-2">{{ __('pages/referral.help_p3') }}</p>
              <p class="mt-2">{{ __('pages/referral.help_p4') }}</p>
              <p class="mt-2">{{ __('pages/referral.help_p5') }}</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div class="mt-2 rounded-[8px] border border-theme bg-theme-card p-3 text-center shadow-[0_8px_20px_rgb(var(--theme-primary))/0.08]">
            <p class="text-scale-micro font-medium text-theme-secondary">{{ __('pages/referral.level_1_title') }}</p>
            <p class="mt-1.5 text-[clamp(1.4rem,6vw,1.8rem)] font-semibold text-[rgb(var(--theme-primary))]">{{ $dashboard['level_1_rate'] }}</p>
            <p class="mt-1 text-scale-micro text-theme-secondary">{{ __('pages/referral.level_1_count_label') }}</p>
            <p class="text-scale-title font-semibold text-theme">{{ $dashboard['level_one_count'] }}</p>
          </div>
          <div class="mt-2 rounded-[8px] border border-theme bg-theme-card p-3 text-center shadow-[0_8px_20px_rgb(var(--theme-primary))/0.08]">
            <p class="text-scale-micro font-medium text-theme-secondary">{{ __('pages/referral.level_2_title') }}</p>
            <p class="mt-1.5 text-[clamp(1.4rem,6vw,1.8rem)] font-semibold text-[rgb(var(--theme-accent))]">{{ $dashboard['level_2_rate'] }}</p>
            <p class="mt-1 text-scale-micro text-theme-secondary">{{ __('pages/referral.level_2_count_label') }}</p>
            <p class="text-scale-title font-semibold text-theme">{{ $dashboard['level_two_count'] }}</p>
          </div>
        </div>
      </div>

      <div class="mt-3 border-t border-theme pt-3">
        <p class="text-scale-micro font-semibold uppercase tracking-[0.12em] text-[rgb(var(--theme-primary))]">{{ __('pages/referral.invite_code') }}</p>
        <div class="mt-1.5 rounded-[8px] border border-theme bg-theme-secondary/20 px-3 py-2.5">
          <p class="break-all text-[clamp(1.05rem,4.3vw,1.35rem)] font-semibold tracking-[0.14em] text-theme">{{ $dashboard['invite_code'] }}</p>
        </div>
      </div>

      <div class="mt-3">
        <p class="text-scale-micro font-semibold uppercase tracking-[0.12em] text-[rgb(var(--theme-primary))]">{{ __('pages/referral.invite_link') }}</p>
        <div class="mt-1.5 rounded-[8px] border border-theme bg-theme-secondary/20 px-3 py-2.5">
          <p id="referral-invite-url" class="break-all text-scale-body leading-5 text-theme-secondary">{{ $dashboard['invite_url'] }}</p>
        </div>
      </div>

      <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
        <button type="button" id="copy-referral-link" data-referral-url="{{ $dashboard['invite_url'] }}" class="text-scale-ui inline-flex min-h-[2.85rem] items-center justify-center rounded-[8px] border border-theme bg-theme-secondary px-4 font-semibold text-theme transition hover:border-[rgb(var(--theme-primary))]/40 hover:text-[rgb(var(--theme-primary))]">
          {{ __('pages/referral.copy_link') }}
        </button>
        <button type="button" id="share-referral-link" data-referral-url="{{ $dashboard['invite_url'] }}" class="text-scale-ui inline-flex min-h-[2.85rem] items-center justify-center rounded-[8px] bg-[rgb(var(--theme-primary))] px-4 font-semibold text-theme-on-primary shadow-[0_12px_25px_rgb(var(--theme-primary))/0.28] transition hover:bg-[rgb(var(--theme-primary))]/90">
          {{ __('pages/referral.share_now') }}
        </button>
      </div>
    </section>
  </main>

  <x-nav.mobile />

  <script>
    const referralButtons = [
      document.getElementById('copy-referral-link'),
      document.getElementById('share-referral-link'),
    ];
    const rewardHelpToggle = document.getElementById('reward-help-toggle');
    const rewardHelpPanel = document.getElementById('reward-help-panel');
    const i18n = {
      copyLabel: @json(__('pages/referral.copy_link')),
      copiedLabel: @json(__('pages/referral.copied')),
      shareText: @json(__('pages/referral.share_text', ['app_name' => config('app.name')])),
      linkCopiedAlert: @json(__('pages/referral.link_copied_alert')),
    };

    const closeRewardHelp = () => {
      if (!rewardHelpPanel || !rewardHelpToggle) return;
      rewardHelpPanel.classList.add('pointer-events-none', 'opacity-0', 'translate-y-1');
      rewardHelpPanel.classList.remove('pointer-events-auto', 'opacity-100', 'translate-y-0');
      rewardHelpToggle.setAttribute('aria-expanded', 'false');
    };

    rewardHelpToggle?.addEventListener('click', (event) => {
      if (!rewardHelpPanel) return;

      event.stopPropagation();
      const shouldExpand = rewardHelpPanel.classList.contains('opacity-0');
      rewardHelpPanel.classList.toggle('pointer-events-none', !shouldExpand);
      rewardHelpPanel.classList.toggle('opacity-0', !shouldExpand);
      rewardHelpPanel.classList.toggle('translate-y-1', !shouldExpand);
      rewardHelpPanel.classList.toggle('pointer-events-auto', shouldExpand);
      rewardHelpPanel.classList.toggle('opacity-100', shouldExpand);
      rewardHelpPanel.classList.toggle('translate-y-0', shouldExpand);
      rewardHelpToggle.setAttribute('aria-expanded', shouldExpand ? 'true' : 'false');
    });

    rewardHelpPanel?.addEventListener('click', (event) => {
      event.stopPropagation();
    });

    document.addEventListener('click', () => {
      closeRewardHelp();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeRewardHelp();
    });

    document.getElementById('copy-referral-link')?.addEventListener('click', async (event) => {
      const url = event.currentTarget.dataset.referralUrl;
      if (!url) return;

      try {
        if (navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(url);
          event.currentTarget.textContent = i18n.copiedLabel;
          setTimeout(() => {
            event.currentTarget.textContent = i18n.copyLabel;
          }, 1500);
          return;
        }
      } catch (error) {
      }

      alert(url);
    });

    document.getElementById('share-referral-link')?.addEventListener('click', async (event) => {
      const url = event.currentTarget.dataset.referralUrl;
      if (!url) return;

      if (typeof navigator.share === 'function') {
        try {
          await navigator.share({
            title: document.title,
            text: i18n.shareText,
            url,
          });
          return;
        } catch (error) {
          if (error instanceof DOMException && error.name === 'AbortError') return;
        }
      }

      try {
        if (navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(url);
          alert(i18n.linkCopiedAlert);
          return;
        }
      } catch (error) {
      }

      alert(url);
    });
  </script>
</body>
</html>
