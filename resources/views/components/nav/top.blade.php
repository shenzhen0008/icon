<header id="top-nav" class="sticky top-0 z-30 border-b border-theme bg-theme-secondary/90 backdrop-blur">
  <div class="mx-auto flex w-full max-w-4xl items-center justify-between px-[clamp(0.75rem,3.5vw,1.5rem)] py-[clamp(0.6rem,2.5vw,1rem)]">
    <a href="/" class="text-scale-ui font-semibold tracking-[0.2em] text-[rgb(var(--theme-primary))]">ICON MARKET</a>

    <nav class="hidden items-center gap-[clamp(0.8rem,3vw,1.5rem)] text-scale-ui md:flex">
      <a href="/" class="{{ request()->is('/') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">首页</a>
      <a href="/products" class="{{ request()->is('products') || request()->is('products/*') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">产品</a>
      <a href="/help" class="{{ request()->is('help') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">帮助</a>
      <button type="button" data-share-entry class="text-theme-secondary transition hover:text-[rgb(var(--theme-primary))]">分享</button>
      <a href="/me" class="{{ request()->is('me') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">我的</a>
      <a href="/support" class="{{ request()->is('support') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">客服</a>
      <a href="/stream-chat" class="{{ request()->is('stream-chat') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">
        <span class="relative inline-flex items-center gap-1">
          Stream Chat
          <span data-stream-chat-unread-dot class="hidden h-2 w-2 rounded-full bg-[rgb(var(--theme-rose))]"></span>
        </span>
      </a>
      @auth
        <a href="/stream-chat-agent" class="{{ request()->is('stream-chat-agent') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">Stream Agent</a>
      @endauth
    </nav>

    <div class="ml-3 inline-flex items-center gap-2 md:ml-4">
      <div class="relative">
        <button
          id="language-toggle"
          type="button"
          aria-label="切换语言"
          aria-haspopup="true"
          aria-expanded="false"
          aria-controls="language-menu"
          class="inline-flex items-center justify-center gap-1 rounded-full p-[0.4rem] text-theme transition hover:bg-[rgb(var(--theme-primary))]/10"
        >
          <img
            src="{{ asset('images/flags/cn.svg') }}"
            alt=""
            class="h-4 w-5 shrink-0 rounded-[2px] object-cover"
            aria-hidden="true"
            data-language-current-flag
          >
          <span class="text-scale-ui font-medium uppercase leading-none text-theme-secondary" data-language-current-code>ZH</span>
        </button>
        <div
          id="language-menu"
          class="absolute right-0 top-full z-40 mt-2 hidden w-max max-w-[calc(100vw-1rem)] overflow-hidden rounded-lg border border-theme bg-theme-card py-1 shadow-xl shadow-[rgb(var(--theme-primary))]/10"
          role="menu"
          aria-labelledby="language-toggle"
        >
          <button type="button" class="flex w-full items-center gap-3 px-4 py-2 text-left text-scale-body text-theme-secondary transition hover:bg-theme-secondary/50 hover:text-theme" role="menuitem" data-language-option data-language-code="ZH" data-language-flag="{{ asset('images/flags/cn.svg') }}"><img src="{{ asset('images/flags/cn.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>中文</span></button>
          <button type="button" class="flex w-full items-center gap-3 px-4 py-2 text-left text-scale-body text-theme-secondary transition hover:bg-theme-secondary/50 hover:text-theme" role="menuitem" data-language-option data-language-code="EN" data-language-flag="{{ asset('images/flags/us.svg') }}"><img src="{{ asset('images/flags/us.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>English</span></button>
          <button type="button" class="flex w-full items-center gap-3 px-4 py-2 text-left text-scale-body text-theme-secondary transition hover:bg-theme-secondary/50 hover:text-theme" role="menuitem" data-language-option data-language-code="JA" data-language-flag="{{ asset('images/flags/jp.svg') }}"><img src="{{ asset('images/flags/jp.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>日本語</span></button>
          <button type="button" class="flex w-full items-center gap-3 px-4 py-2 text-left text-scale-body text-theme-secondary transition hover:bg-theme-secondary/50 hover:text-theme" role="menuitem" data-language-option data-language-code="KO" data-language-flag="{{ asset('images/flags/kr.svg') }}"><img src="{{ asset('images/flags/kr.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>한국어</span></button>
          <button type="button" class="flex w-full items-center gap-3 px-4 py-2 text-left text-scale-body text-theme-secondary transition hover:bg-theme-secondary/50 hover:text-theme" role="menuitem" data-language-option data-language-code="DE" data-language-flag="{{ asset('images/flags/de.svg') }}"><img src="{{ asset('images/flags/de.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>Deutsch</span></button>
          <button type="button" class="flex w-full items-center gap-3 px-4 py-2 text-left text-scale-body text-theme-secondary transition hover:bg-theme-secondary/50 hover:text-theme" role="menuitem" data-language-option data-language-code="FR" data-language-flag="{{ asset('images/flags/fr.svg') }}"><img src="{{ asset('images/flags/fr.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>Français</span></button>
          <button type="button" class="flex w-full items-center gap-3 px-4 py-2 text-left text-scale-body text-theme-secondary transition hover:bg-theme-secondary/50 hover:text-theme" role="menuitem" data-language-option data-language-code="PT" data-language-flag="{{ asset('images/flags/br.svg') }}"><img src="{{ asset('images/flags/br.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>Português</span></button>
          <button type="button" class="flex w-full items-center gap-3 px-4 py-2 text-left text-scale-body text-theme-secondary transition hover:bg-theme-secondary/50 hover:text-theme" role="menuitem" data-language-option data-language-code="ES" data-language-flag="{{ asset('images/flags/es.svg') }}"><img src="{{ asset('images/flags/es.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>Español</span></button>
        </div>
      </div>
      <button
        id="theme-toggle"
        type="button"
        class="inline-flex items-center justify-center rounded-full p-[0.4rem] text-theme transition hover:bg-[rgb(var(--theme-primary))]/10"
      >
        <img src="{{ asset('images/assets/sun.svg') }}" alt="" class="h-[1.35rem] w-[1.35rem] shrink-0 object-contain" aria-hidden="true">
      </button>
    </div>
  </div>
</header>

<script>
  const languageToggle = document.getElementById('language-toggle');
  const languageMenu = document.getElementById('language-menu');
  const languageCurrentFlag = document.querySelector('[data-language-current-flag]');
  const languageCurrentCode = document.querySelector('[data-language-current-code]');
  const languageOptions = document.querySelectorAll('[data-language-option]');

  const closeLanguageMenu = () => {
    if (!languageToggle || !languageMenu) return;
    languageMenu.classList.add('hidden');
    languageToggle.setAttribute('aria-expanded', 'false');
  };

  const toggleLanguageMenu = () => {
    if (!languageToggle || !languageMenu) return;
    const nextExpanded = languageMenu.classList.contains('hidden');
    languageMenu.classList.toggle('hidden', !nextExpanded);
    languageToggle.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
  };

  languageToggle?.addEventListener('click', (event) => {
    event.stopPropagation();
    toggleLanguageMenu();
  });

  languageOptions.forEach((option) => {
    option.addEventListener('click', () => {
      const nextFlag = option.getAttribute('data-language-flag');
      const nextCode = option.getAttribute('data-language-code');
      if (nextFlag && languageCurrentFlag instanceof HTMLImageElement) {
        languageCurrentFlag.src = nextFlag;
      }
      if (nextCode && languageCurrentCode) {
        languageCurrentCode.textContent = nextCode;
      }
      closeLanguageMenu();
    });
  });

  document.addEventListener('click', (event) => {
    if (!languageToggle || !languageMenu) return;
    if (languageToggle.contains(event.target) || languageMenu.contains(event.target)) return;
    closeLanguageMenu();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeLanguageMenu();
  });

  document.querySelectorAll('[data-share-entry]').forEach((button) => {
    button.addEventListener('click', async () => {
      const sharePayload = {
        title: document.title,
        text: 'Icon Market',
        url: window.location.href,
      };

      if (typeof navigator.share === 'function') {
        try {
          await navigator.share(sharePayload);
          return;
        } catch (error) {
          if (error instanceof DOMException && error.name === 'AbortError') return;
        }
      }

      try {
        if (navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(window.location.href);
          alert('链接已复制');
          return;
        }
      } catch (error) {
      }

      alert(window.location.href);
    });
  });

  // 主题切换功能
  document.getElementById('theme-toggle').addEventListener('click', () => {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'tech' ? 'business' : 'tech';
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
  });

  // 页面加载时恢复主题
  const savedTheme = localStorage.getItem('theme') || 'business';
  document.documentElement.setAttribute('data-theme', savedTheme);

  // 同步顶部/底部导航与可视区域数据，避免移动端键盘弹起后布局溢出。
  const syncLayoutInsets = () => {
    const topNav = document.getElementById('top-nav');
    const mobileNav = document.getElementById('mobile-nav');
    const visualViewport = window.visualViewport;
    const topHeight = topNav ? `${topNav.offsetHeight}px` : '68px';
    const mobileHeight = mobileNav ? `${mobileNav.offsetHeight}px` : '68px';
    const viewportHeight = visualViewport ? `${visualViewport.height}px` : `${window.innerHeight}px`;
    const keyboardInset = visualViewport
      ? `${Math.max(0, window.innerHeight - visualViewport.height - visualViewport.offsetTop)}px`
      : '0px';
    document.documentElement.style.setProperty('--top-nav-height', topHeight);
    document.documentElement.style.setProperty('--mobile-nav-height', mobileHeight);
    document.documentElement.style.setProperty('--app-vh', viewportHeight);
    document.documentElement.style.setProperty('--chat-keyboard-inset', keyboardInset);
  };

  syncLayoutInsets();
  window.addEventListener('load', syncLayoutInsets);
  window.addEventListener('resize', syncLayoutInsets);
  window.visualViewport?.addEventListener('resize', syncLayoutInsets);
  window.visualViewport?.addEventListener('scroll', syncLayoutInsets);
</script>

<script type="module">
  const streamNotifyBootstrapKey = 'stream_chat_notify_bootstrap_ready';
  const shouldBootstrapNotify = localStorage.getItem(streamNotifyBootstrapKey) === '1';

  if (!window.location.pathname.startsWith('/stream-chat') && shouldBootstrapNotify && !window.IconMarketStreamNotify?.ready) {
        const state = {
          ready: true,
          client: null,
          channel: null,
          userId: '',
          unreadCount: 0,
          reconnectTimer: null,
          disposed: false,
          channelSubscribed: false,
          baseTitle: document.title,
        };

        window.IconMarketStreamNotify = state;
        const unreadStorageKey = 'stream_chat_unread_count';
        const initialUnread = Number(localStorage.getItem(unreadStorageKey) || '0');
        state.unreadCount = Number.isFinite(initialUnread) ? Math.max(0, initialUnread) : 0;

        const renderUnreadBadges = () => {
          const hasUnread = state.unreadCount > 0;
          document.querySelectorAll('[data-stream-chat-unread-dot]').forEach((node) => {
            node.classList.toggle('hidden', !hasUnread);
          });
        };

        const setUnreadCount = (count) => {
          state.unreadCount = Math.max(0, count);
          localStorage.setItem(unreadStorageKey, String(state.unreadCount));
          renderUnreadBadges();
          updateTitle();
        };

        const updateTitle = () => {
          document.title = state.unreadCount > 0
            ? `(${state.unreadCount}) ${state.baseTitle}`
            : state.baseTitle;
        };

        const beep = async () => {
          const soundEnabled = localStorage.getItem('stream_chat_sound_enabled') === '1';
          if (!soundEnabled) return;
          const AudioCtx = window.AudioContext || window.webkitAudioContext;
          if (!AudioCtx) return;
          if (!state.audioCtx) {
            state.audioCtx = new AudioCtx();
          }
          if (state.audioCtx.state === 'suspended') {
            await state.audioCtx.resume();
          }
          const oscillator = state.audioCtx.createOscillator();
          const gainNode = state.audioCtx.createGain();
          oscillator.type = 'sine';
          oscillator.frequency.value = 880;
          gainNode.gain.value = 0.02;
          oscillator.connect(gainNode);
          gainNode.connect(state.audioCtx.destination);
          oscillator.start();
          oscillator.stop(state.audioCtx.currentTime + 0.12);
        };

        const showBrowserNotification = (messageText) => {
          if (!('Notification' in window)) return;
          if (Notification.permission !== 'granted') return;
          if (!document.hidden) return;

          const notification = new Notification('Icon Market 客服新消息', {
            body: messageText || '你有一条新的客服消息',
          });
          setTimeout(() => notification.close(), 5000);
        };

        const scheduleReconnect = () => {
          if (state.disposed || state.reconnectTimer !== null) return;
          state.reconnectTimer = window.setTimeout(() => {
            state.reconnectTimer = null;
            bootstrap().catch(() => scheduleReconnect());
          }, 3000);
        };

        const bootstrap = async () => {
          if (state.disposed) return;

          const tokenRes = await fetch('/stream-chat/notify-token', {
            headers: { Accept: 'application/json' },
          });
          if (tokenRes.status === 404) {
            localStorage.removeItem(streamNotifyBootstrapKey);
            return;
          }
          if (!tokenRes.ok) {
            scheduleReconnect();
            return;
          }

          const payload = await tokenRes.json();
          const { StreamChat } = await import('https://cdn.jsdelivr.net/npm/stream-chat/+esm');
          const client = state.client ?? StreamChat.getInstance(payload.apiKey);

          if (!state.client) {
            await client.connectUser(payload.user, payload.token);
            state.client = client;
            state.userId = payload.user.id;
            client.on('connection.changed', (event) => {
              if (!event.online) scheduleReconnect();
            });
          }

          const channel = client.channel(payload.channel.type, payload.channel.id, {
            name: payload.channel.name,
            members: payload.channel.members,
          });
          state.channel = channel;

          await channel.watch({ watch: true, state: true });

          if (!state.channelSubscribed) {
            state.channelSubscribed = true;
            channel.on('message.new', (event) => {
              if (event.message?.user?.id === state.userId) return;
              setUnreadCount(state.unreadCount + 1);
              beep().catch(() => {});
              showBrowserNotification(event.message?.text);
            });
          }
        };
        renderUnreadBadges();
        updateTitle();

        window.addEventListener('storage', (event) => {
          if (event.key !== unreadStorageKey) return;
          const nextValue = Number(event.newValue || '0');
          state.unreadCount = Number.isFinite(nextValue) ? Math.max(0, nextValue) : 0;
          renderUnreadBadges();
          updateTitle();
        });

        window.addEventListener('stream-chat-unread-updated', (event) => {
          const nextValue = Number(event.detail?.count ?? 0);
          state.unreadCount = Number.isFinite(nextValue) ? Math.max(0, nextValue) : 0;
          renderUnreadBadges();
          updateTitle();
        });

        window.addEventListener('beforeunload', () => {
          state.disposed = true;
          if (state.reconnectTimer !== null) {
            window.clearTimeout(state.reconnectTimer);
          }
          if (state.client) {
            state.client.disconnectUser().catch(() => {});
          }
        });

        bootstrap().catch(() => scheduleReconnect());
  }
</script>
