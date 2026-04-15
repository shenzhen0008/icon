<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>邀请分享 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="flex min-h-[calc(var(--app-vh,100dvh)-var(--top-nav-height,4rem)-var(--mobile-nav-height,4rem))] w-full flex-col pt-0 md:mx-auto md:max-w-4xl md:min-h-0 md:px-4 md:pb-12 md:pt-4">
    <section class="relative overflow-hidden border border-[rgb(var(--theme-primary))]/20 bg-gradient-to-br from-[rgb(var(--theme-primary))]/85 via-[rgb(var(--theme-accent))]/75 to-[rgb(var(--theme-primary))]/70 px-5 pb-32 pt-6 text-white shadow-[0_20px_45px_rgb(var(--theme-primary))/0.24] md:rounded-[8px]">
      <div class="relative z-10 max-w-[15rem] md:max-w-[16rem]">
        <p class="text-scale-micro font-semibold uppercase tracking-[0.12em] text-white/80">Invite & Earn</p>
        <h1 class="mt-2 text-[clamp(1.95rem,8vw,2.65rem)] font-semibold leading-[1.02]">邀请分享</h1>
        <p class="mt-3 text-scale-body leading-6 text-white/85">
          邀请好友加入，一级返佣 5%，二级返佣 2%，核心信息一页看完，分享更直接。
        </p>
      </div>

      <div class="pointer-events-none absolute inset-x-0 bottom-0 top-[5.75rem]">
        <div class="absolute left-[-1.75rem] top-5 h-44 w-44 rounded-full bg-white/14"></div>
        <div class="absolute right-[-2.25rem] top-12 h-52 w-52 rounded-full bg-white/12"></div>
        <div class="absolute left-1/2 top-10 h-32 w-32 -translate-x-1/2 rounded-full bg-[#8eb1ff]/35 blur-xl"></div>

        <img
          src="{{ asset('images/card.png') }}"
          alt=""
          aria-hidden="true"
          class="absolute bottom-4 left-4 h-28 w-auto opacity-90 sm:h-32"
        >
        <img
          src="{{ asset('images/coin.png') }}"
          alt=""
          aria-hidden="true"
          class="absolute right-7 top-2 h-14 w-14 rotate-[12deg] drop-shadow-[0_10px_18px_rgba(255,177,0,0.35)]"
        >
        <img
          src="{{ asset('images/coin.png') }}"
          alt=""
          aria-hidden="true"
          class="absolute right-20 top-20 h-12 w-12 -rotate-[10deg] opacity-95 drop-shadow-[0_10px_18px_rgba(255,177,0,0.35)]"
        >
      </div>
    </section>

    <section class="relative -mt-[5.5rem] mx-4 flex-1 rounded-[8px] border border-theme bg-theme-card p-5 shadow-[0_20px_45px_rgb(var(--theme-primary))/0.12] md:mx-0 md:flex-none">
      <div class="rounded-[8px] border border-theme bg-theme-secondary/25 p-4">
        <div class="relative flex items-center justify-between gap-3">
          <p class="text-scale-body font-semibold text-theme">奖励说明</p>
          <button
            type="button"
            id="reward-help-toggle"
            aria-label="查看奖励说明"
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
            <div class="relative max-h-[22rem] overflow-y-auto pr-1">
              <p class="font-semibold text-theme">Is there a reward for inviting friends?</p>
              <p class="mt-2">
                Yes, you can invite your friends to join AI Smart Contracts through your personal link and successfully invite your friends to join AI Smart Contracts. You will get an invitation bonus provided by AI Smart Contracts platform. The specific invitation rewards are as follows:
              </p>
              <p class="mt-2">
                ① User A invites User B (User A can get 5% of the revenue from User B's participation in AI Smart Contracts).
              </p>
              <p class="mt-2">
                ② User B invites User C (User B can get 5% of the revenue from User C's participation in AI Smart Contracts, while User A can get 3% of the revenue from User C's participation in AI Smart Contracts).
              </p>
              <p class="mt-2">
                ③ User C invites User D (User C can get 5% of the revenue from User D's participation in AI smart contracts, User B can get 3% of the revenue from User D's participation in AI smart contracts, and User A can get 1% of the revenue from User D's .)
              </p>
              <p class="mt-2">
                Example: User D participates in an AI smart contract to earn 100 USDT. User A will receive an invitation bonus of 1 USDT from the AI Smart Contracts platform. User B will receive an invitation bonus of 3 USDT from the AI Smart Contracts platform. User C will receive an invitation bonus of 5 USDT from the AI Smart Contracts Platform. All rewards are provided by the AI Smart Contracts Platform and do not affect the earnings of any user.
              </p>
              <p class="mt-4 font-semibold text-theme">邀请好友有奖励吗？</p>
              <p class="mt-2">
                有的。您可以通过个人专属链接邀请好友加入 AI 智能合约，成功邀请后，您将获得 AI 智能合约平台提供的邀请奖励。具体邀请奖励规则如下：
              </p>
              <p class="mt-2">
                ① 用户 A 邀请用户 B（用户 A 可获得用户 B 参与 AI 智能合约收益的 5%）
              </p>
              <p class="mt-2">
                ② 用户 B 邀请用户 C（用户 B 可获得用户 C 参与 AI 智能合约收益的 5%，同时用户 A 可获得用户 C 参与 AI 智能合约收益的 3%）
              </p>
              <p class="mt-2">
                ③ 用户 C 邀请用户 D（用户 C 可获得用户 D 参与 AI 智能合约收益的 5%，用户 B 可获得用户 D 参与 AI 智能合约收益的 3%，用户 A 可获得用户 D 参与 AI 智能合约收益的 1%）
              </p>
              <p class="mt-2">
                举例说明：用户 D 参与 AI 智能合约赚取 100 USDT，用户 A 将从 AI 智能合约平台获得 1 USDT 的邀请奖励，用户 B 将从 AI 智能合约平台获得 3 USDT 的邀请奖励，用户 C 将从 AI 智能合约平台获得 5 USDT 的邀请奖励。所有奖励均由 AI 智能合约平台提供，不会影响任何用户自身的收益。
              </p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="mt-3 rounded-[8px] border border-theme bg-theme-card p-4 text-center shadow-[0_8px_20px_rgb(var(--theme-primary))/0.08]">
            <p class="text-scale-micro font-medium text-theme-secondary">一级奖励 Direct</p>
            <p class="mt-2 text-[clamp(1.55rem,6.5vw,2rem)] font-semibold text-[rgb(var(--theme-primary))]">{{ $dashboard['level_1_rate'] }}</p>
            <p class="mt-1 text-scale-micro text-theme-secondary">一级邀请</p>
            <p class="mt-1 text-scale-title font-semibold text-theme">{{ $dashboard['level_one_count'] }}</p>
          </div>
          <div class="mt-3 rounded-[8px] border border-theme bg-theme-card p-4 text-center shadow-[0_8px_20px_rgb(var(--theme-primary))/0.08]">
            <p class="text-scale-micro font-medium text-theme-secondary">二级奖励 Indirect</p>
            <p class="mt-2 text-[clamp(1.55rem,6.5vw,2rem)] font-semibold text-[rgb(var(--theme-accent))]">{{ $dashboard['level_2_rate'] }}</p>
            <p class="mt-1 text-scale-micro text-theme-secondary">二级邀请</p>
            <p class="mt-1 text-scale-title font-semibold text-theme">{{ $dashboard['level_two_count'] }}</p>
          </div>
        </div>
      </div>

      <div class="mt-4 border-t border-theme pt-4">
        <p class="text-scale-micro font-semibold uppercase tracking-[0.12em] text-[rgb(var(--theme-primary))]">邀请码</p>
        <div class="mt-2 rounded-[8px] border border-theme bg-theme-secondary/20 px-4 py-3">
          <p class="break-all text-[clamp(1.2rem,5vw,1.55rem)] font-semibold tracking-[0.18em] text-theme">{{ $dashboard['invite_code'] }}</p>
        </div>
      </div>

      <div class="mt-4">
        <p class="text-scale-micro font-semibold uppercase tracking-[0.12em] text-[rgb(var(--theme-primary))]">邀请链接</p>
        <div class="mt-2 rounded-[8px] border border-theme bg-theme-secondary/20 px-4 py-3">
          <p id="referral-invite-url" class="break-all text-scale-body leading-6 text-theme-secondary">{{ $dashboard['invite_url'] }}</p>
        </div>
      </div>

      <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <button type="button" id="copy-referral-link" data-referral-url="{{ $dashboard['invite_url'] }}" class="text-scale-ui inline-flex min-h-[3.25rem] items-center justify-center rounded-[8px] border border-theme bg-theme-secondary px-4 font-semibold text-theme transition hover:border-[rgb(var(--theme-primary))]/40 hover:text-[rgb(var(--theme-primary))]">
          复制链接
        </button>
        <button type="button" id="share-referral-link" data-referral-url="{{ $dashboard['invite_url'] }}" class="text-scale-ui inline-flex min-h-[3.25rem] items-center justify-center rounded-[8px] bg-[rgb(var(--theme-primary))] px-4 font-semibold text-theme-on-primary shadow-[0_12px_25px_rgb(var(--theme-primary))/0.28] transition hover:bg-[rgb(var(--theme-primary))]/90">
          立即分享
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
          event.currentTarget.textContent = '已复制';
          setTimeout(() => {
            event.currentTarget.textContent = '复制链接';
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
            text: 'Icon Market 邀请分享',
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
          alert('链接已复制');
          return;
        }
      } catch (error) {
      }

      alert(url);
    });
  </script>
</body>
</html>
