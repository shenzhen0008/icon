<header class="sticky top-0 z-30 border-b border-white/10 bg-slate-950/90 backdrop-blur">
  <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
    <a href="/" class="text-sm font-semibold tracking-[0.2em] text-cyan-300">ICON MARKET</a>

    <nav class="hidden items-center gap-6 text-sm md:flex">
      <a href="/" class="{{ request()->is('/') ? 'text-cyan-200' : 'text-slate-300 hover:text-cyan-200' }}">首页</a>
      <a href="/admin" class="{{ request()->is('admin') || request()->is('admin/*') ? 'text-cyan-200' : 'text-slate-300 hover:text-cyan-200' }}">后台</a>
      <a href="/products" class="{{ request()->is('products') || request()->is('products/*') ? 'text-cyan-200' : 'text-slate-300 hover:text-cyan-200' }}">产品</a>
      <a href="/recharge" class="{{ request()->is('recharge') ? 'text-cyan-200' : 'text-slate-300 hover:text-cyan-200' }}">充值</a>
      <a href="/me" class="{{ request()->is('me') ? 'text-cyan-200' : 'text-slate-300 hover:text-cyan-200' }}">我的</a>
      <a href="/support" class="{{ request()->is('support') ? 'text-cyan-200' : 'text-slate-300 hover:text-cyan-200' }}">客服</a>
      <a href="/stream-chat" class="{{ request()->is('stream-chat') ? 'text-cyan-200' : 'text-slate-300 hover:text-cyan-200' }}">
        <span class="relative inline-flex items-center gap-1">
          Stream Chat
          <span data-stream-chat-unread-dot class="hidden h-2 w-2 rounded-full bg-rose-400"></span>
        </span>
      </a>
      @auth
        <a href="/stream-chat-agent" class="{{ request()->is('stream-chat-agent') ? 'text-cyan-200' : 'text-slate-300 hover:text-cyan-200' }}">Stream Agent</a>
      @endauth
    </nav>
  </div>
</header>

<script type="module">
  if (!window.location.pathname.startsWith('/stream-chat') && !window.IconMarketStreamNotify?.ready) {
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
