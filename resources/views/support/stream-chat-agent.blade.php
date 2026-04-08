<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Stream Chat Agent | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-theme text-theme">
  <x-nav.top />

  <main class="mx-auto w-full max-w-6xl px-6 pb-[calc(var(--mobile-nav-height,4.25rem)+1.5rem+env(safe-area-inset-bottom))] pt-8 md:pb-10">
    @if ($streamEnabled)
      <section class="grid overflow-hidden rounded-2xl border border-[rgb(var(--theme-primary))]/20 bg-theme-card shadow-xl shadow-[rgb(var(--theme-primary))]/10 md:grid-cols-[17rem_1fr]">
        <aside id="agent-mobile-list-view" class="md:border-r md:border-theme">
          <div class="border-b border-theme px-4 py-3 text-xs text-theme-secondary">会话列表</div>
          <div id="agent-channel-list" class="h-[calc(var(--app-vh,100dvh)-19rem)] min-h-[360px] overflow-y-auto"></div>
        </aside>
        <div id="agent-mobile-chat-view" class="hidden min-h-[420px] flex-col md:flex">
          <div class="flex items-center justify-between border-b border-theme px-4 py-3 text-xs text-theme-secondary md:hidden">
            <button id="agent-mobile-back" type="button" class="inline-flex items-center gap-1 rounded-lg border border-theme px-2.5 py-1.5 text-xs text-theme-secondary hover:border-[rgb(var(--theme-primary))] hover:text-[rgb(var(--theme-primary))]">返回列表</button>
            <button id="agent-open-channel-drawer" type="button" class="inline-flex items-center gap-1 rounded-lg border border-theme px-2.5 py-1.5 text-xs text-theme-secondary hover:border-[rgb(var(--theme-primary))] hover:text-[rgb(var(--theme-primary))]">切换会话</button>
          </div>
          <div id="agent-status" class="border-b border-theme px-4 py-3 text-xs text-theme-secondary">正在连接客服工作台...</div>
          <div id="agent-messages" class="h-[calc(var(--app-vh,100dvh)-24rem)] min-h-[260px] overflow-y-auto px-4 py-4"></div>
          <form id="agent-chat-form" class="border-t border-theme p-3">
            <div class="flex min-w-0 items-center gap-2">
              <label for="agent-chat-file" class="inline-flex shrink-0 cursor-pointer items-center whitespace-nowrap rounded-lg border border-theme px-3 py-2 text-xs text-theme-secondary transition hover:border-[rgb(var(--theme-primary))] hover:text-[rgb(var(--theme-primary))]">图片</label>
              <input id="agent-chat-file" type="file" accept="image/*" class="hidden">
              <input
                id="agent-chat-input"
                type="text"
                maxlength="500"
                placeholder="输入回复内容..."
                class="flex-1 min-w-0 rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-base text-theme placeholder:text-theme-secondary focus:border-[rgb(var(--theme-primary))] focus:outline-none md:text-sm"
              >
              <button
                type="submit"
                class="whitespace-nowrap rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2 text-sm font-semibold text-theme-secondary transition hover:bg-[rgb(var(--theme-primary))]/80"
              >发送</button>
            </div>
          </form>
        </div>
      </section>

      <div id="agent-channel-drawer" class="fixed inset-0 z-40 hidden md:hidden">
        <button id="agent-channel-drawer-mask" type="button" class="absolute inset-0 bg-black/45"></button>
        <section class="absolute inset-x-0 bottom-0 max-h-[70vh] overflow-hidden rounded-t-2xl border border-theme bg-theme-card shadow-2xl shadow-[rgb(var(--theme-primary))]/20">
          <div class="flex items-center justify-between border-b border-theme px-4 py-3">
            <p class="text-sm font-semibold text-theme">切换会话</p>
            <button id="agent-close-channel-drawer" type="button" class="rounded-lg border border-theme px-2.5 py-1 text-xs text-theme-secondary hover:border-[rgb(var(--theme-primary))] hover:text-[rgb(var(--theme-primary))]">关闭</button>
          </div>
          <div id="agent-channel-drawer-list" class="max-h-[55vh] overflow-y-auto"></div>
        </section>
      </div>

      <aside id="agent-chat-sound-prompt" class="fixed right-3 top-20 z-40 hidden w-56 rounded-xl border border-[rgb(var(--theme-primary))]/30 bg-theme-card p-3 shadow-lg shadow-[rgb(var(--theme-primary))]/20 md:right-6 md:top-24">
        <p class="text-xs text-theme-secondary">开启消息提醒音？收到访客新消息时会播放提示音。</p>
        <div class="mt-2 flex items-center justify-end gap-2">
          <button id="agent-chat-sound-dismiss" type="button" class="rounded-lg border border-theme px-2.5 py-1 text-xs text-theme-secondary hover:border-[rgb(var(--theme-primary))]">稍后</button>
          <button id="agent-chat-sound-enable" type="button" class="rounded-lg bg-[rgb(var(--theme-primary))] px-2.5 py-1 text-xs font-semibold text-theme-on-primary hover:bg-[rgb(var(--theme-primary))]/80">开启</button>
        </div>
      </aside>
    @else
      <section class="rounded-2xl border border-dashed border-theme bg-theme-card p-8 text-sm text-theme-secondary">
        Stream Chat 尚未配置完成，请先设置 API Key 和 Secret。
      </section>
    @endif
  </main>

  <x-nav.mobile />

  @if ($streamEnabled)
    <script type="module">
      import { StreamChat } from 'https://cdn.jsdelivr.net/npm/stream-chat/+esm';

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
      const statusEl = document.getElementById('agent-status');
      const listEl = document.getElementById('agent-channel-list');
      const drawerListEl = document.getElementById('agent-channel-drawer-list');
      const messagesEl = document.getElementById('agent-messages');
      const formEl = document.getElementById('agent-chat-form');
      const inputEl = document.getElementById('agent-chat-input');
      const fileEl = document.getElementById('agent-chat-file');
      const listViewEl = document.getElementById('agent-mobile-list-view');
      const chatViewEl = document.getElementById('agent-mobile-chat-view');
      const backButtonEl = document.getElementById('agent-mobile-back');
      const openDrawerButtonEl = document.getElementById('agent-open-channel-drawer');
      const drawerEl = document.getElementById('agent-channel-drawer');
      const drawerMaskEl = document.getElementById('agent-channel-drawer-mask');
      const closeDrawerButtonEl = document.getElementById('agent-close-channel-drawer');
      const soundPromptEl = document.getElementById('agent-chat-sound-prompt');
      const soundEnableEl = document.getElementById('agent-chat-sound-enable');
      const soundDismissEl = document.getElementById('agent-chat-sound-dismiss');
      const pendingUploads = new Map();
      let currentUserId = '';
      let soundEnabled = localStorage.getItem('stream_chat_agent_sound_enabled') === '1';
      const audioCtx = window.AudioContext ? new window.AudioContext() : null;

      let activeChannel = null;
      let listSubscription = null;
      let activeSubscription = null;
      const isMobile = () => window.matchMedia('(max-width: 767px)').matches;

      const setStatus = (message) => {
        if (statusEl) statusEl.textContent = message;
      };

      const showListView = () => {
        if (!isMobile()) return;
        listViewEl?.classList.remove('hidden');
        chatViewEl?.classList.add('hidden');
      };

      const showChatView = () => {
        if (!isMobile()) return;
        chatViewEl?.classList.remove('hidden');
        listViewEl?.classList.add('hidden');
      };

      const openChannelDrawer = () => {
        if (!isMobile()) return;
        drawerEl?.classList.remove('hidden');
      };

      const closeChannelDrawer = () => {
        drawerEl?.classList.add('hidden');
      };

      const resetMessages = () => {
        if (messagesEl) messagesEl.innerHTML = '';
      };

      const buildMessageNode = (message) => {
        const isAgent = message.user?.id?.startsWith('support_agent_') || message.user?.id?.startsWith('agent_');
        const displayName = isAgent ? '我' : (message.user?.name || '访客');
        const wrapper = document.createElement('div');
        wrapper.className = `mb-3 flex ${isAgent ? 'justify-end' : 'justify-start'}`;

        const content = document.createElement('div');
        content.className = 'max-w-[80%]';

        const sender = document.createElement('div');
        sender.className = `mb-1 text-xs ${isAgent ? 'text-right text-[rgb(var(--theme-primary))]' : 'text-theme-secondary'}`;
        sender.textContent = `${displayName}:`;
        content.appendChild(sender);

        if (message.text) {
          const bubble = document.createElement('div');
          bubble.className = isAgent
            ? 'rounded-lg bg-[rgb(var(--theme-primary))]/20 px-3 py-2 text-sm text-theme'
            : 'rounded-lg bg-theme-secondary/80 px-3 py-2 text-sm text-theme';
          bubble.textContent = message.text;
          content.appendChild(bubble);
        }

        const attachments = Array.isArray(message.attachments) ? message.attachments : [];
        attachments.forEach((attachment) => {
          const imageUrl = attachment?.image_url || attachment?.thumb_url || attachment?.asset_url;
          if (attachment?.type !== 'image' || !imageUrl) return;

          const image = document.createElement('img');
          image.src = imageUrl;
          image.alt = attachment?.title || 'uploaded image';
          image.className = 'mt-2 max-h-64 rounded-lg border border-theme object-contain';
          content.appendChild(image);
        });

        if (content.childElementCount === 0) return;
        wrapper.appendChild(content);
        return wrapper;
      };

      const renderMessage = (message) => {
        if (!messagesEl || !message) return;
        const node = buildMessageNode(message);
        if (node) messagesEl.appendChild(node);
      };

      const renderHistory = (messages) => {
        if (!messagesEl) return;
        messagesEl.innerHTML = '';
        messages.forEach(renderMessage);
        messagesEl.scrollTop = messagesEl.scrollHeight;
      };

      const beep = async () => {
        if (!audioCtx || !soundEnabled) return;
        if (audioCtx.state === 'suspended') {
          await audioCtx.resume();
        }
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        oscillator.type = 'sine';
        oscillator.frequency.value = 880;
        gainNode.gain.value = 0.02;
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        oscillator.start();
        oscillator.stop(audioCtx.currentTime + 0.12);
      };

      const showSoundPrompt = () => {
        if (!soundPromptEl || soundEnabled) return;
        soundPromptEl.classList.remove('hidden');
      };

      const hideSoundPrompt = () => {
        if (!soundPromptEl) return;
        soundPromptEl.classList.add('hidden');
      };

      soundEnableEl?.addEventListener('click', async () => {
        soundEnabled = true;
        localStorage.setItem('stream_chat_agent_sound_enabled', '1');
        try {
          await beep();
          setStatus('提醒音已开启。');
        } catch (_) {
          setStatus('提醒音开启失败，请再次点击。');
        }
        hideSoundPrompt();
      });

      soundDismissEl?.addEventListener('click', () => {
        hideSoundPrompt();
      });

      showSoundPrompt();

      const createUploadingNode = () => {
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-3 flex justify-end';
        wrapper.dataset.uploading = '1';

        const content = document.createElement('div');
        content.className = 'max-w-[80%]';

        const sender = document.createElement('div');
        sender.className = 'mb-1 text-right text-xs text-[rgb(var(--theme-primary))]';
        sender.textContent = '我:';
        content.appendChild(sender);

        const skeleton = document.createElement('div');
        skeleton.className = 'h-40 w-40 max-w-full animate-pulse rounded-lg border border-theme bg-theme-secondary/60 blur-[1px]';
        content.appendChild(skeleton);

        const hint = document.createElement('div');
        hint.className = 'mt-1 text-right text-xs text-theme-secondary';
        hint.textContent = '图片上传中...';
        content.appendChild(hint);

        wrapper.appendChild(content);
        return wrapper;
      };

      const updateActiveChannelHighlight = () => {
        document.querySelectorAll('[data-agent-channel-item]').forEach((node) => {
          const isActive = node.dataset.channelId === activeChannel?.id;
          node.classList.toggle('bg-[rgb(var(--theme-primary))]/20', isActive);
          node.classList.toggle('text-[rgb(var(--theme-primary))]', isActive);
          node.classList.toggle('text-theme', !isActive);
        });
      };

      const activateChannel = async (channel) => {
        if (!channel) return;
        if (activeSubscription) {
          activeSubscription.unsubscribe();
          activeSubscription = null;
        }

        activeChannel = channel;
        await channel.watch();
        renderHistory(channel.state.messages);
        updateActiveChannelHighlight();

        setStatus(`当前会话：${channel.data?.name || channel.id}`);
        showChatView();
        closeChannelDrawer();

        activeSubscription = channel.on('message.new', (event) => {
          const localUploadId = event.message?.local_upload_id;
          if (localUploadId && pendingUploads.has(localUploadId) && messagesEl) {
            const oldNode = pendingUploads.get(localUploadId);
            const newNode = buildMessageNode(event.message);
            if (newNode) {
              oldNode.replaceWith(newNode);
            } else {
              oldNode.remove();
            }
            pendingUploads.delete(localUploadId);
            messagesEl.scrollTop = messagesEl.scrollHeight;
            return;
          }

          renderMessage(event.message);
          if (messagesEl) messagesEl.scrollTop = messagesEl.scrollHeight;
          if (event.message?.user?.id !== currentUserId) {
            beep().catch(() => {});
          }
        });
      };

      const renderChannelButtons = (container, channels) => {
        if (!container) return;
        container.innerHTML = '';

        if (channels.length === 0) {
          const empty = document.createElement('div');
          empty.className = 'px-4 py-4 text-sm text-theme-secondary';
          empty.textContent = '暂无访客会话。';
          container.appendChild(empty);
          return;
        }

        channels.forEach((channel) => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.dataset.agentChannelItem = '1';
          btn.dataset.channelId = channel.id;
          btn.className = 'block w-full border-b border-theme px-4 py-3 text-left text-sm text-theme transition hover:bg-theme-secondary';
          btn.textContent = channel.data?.name || channel.id;
          btn.addEventListener('click', () => {
            activateChannel(channel);
          });
          container.appendChild(btn);
        });
      };

      const renderChannelList = async (channels) => {
        renderChannelButtons(listEl, channels);
        renderChannelButtons(drawerListEl, channels);

        if (channels.length === 0) {
          resetMessages();
          setStatus('等待新的访客消息...');
          showListView();
          closeChannelDrawer();
          return;
        }

        const activeChannelId = activeChannel?.id;
        const matched = channels.find((channel) => channel.id === activeChannelId);
        if (matched) {
          await activateChannel(matched);
          return;
        }

        if (!activeChannel && isMobile()) {
          updateActiveChannelHighlight();
          setStatus('请选择会话开始接待...');
          showListView();
          closeChannelDrawer();
          return;
        }

        if (!activeChannel) {
          await activateChannel(channels[0]);
        } else {
          activeChannel = channels[0];
          await activateChannel(channels[0]);
        }
      };

      backButtonEl?.addEventListener('click', () => {
        showListView();
      });
      openDrawerButtonEl?.addEventListener('click', openChannelDrawer);
      closeDrawerButtonEl?.addEventListener('click', closeChannelDrawer);
      drawerMaskEl?.addEventListener('click', closeChannelDrawer);

      const connectAgent = async () => {
        const tokenResponse = await fetch('/stream-chat-agent/token', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
          body: JSON.stringify({}),
        });

        if (!tokenResponse.ok) {
          throw new Error('无法获取客服工作台令牌。');
        }

        const payload = await tokenResponse.json();
        const client = StreamChat.getInstance(payload.apiKey);
        await client.connectUser(payload.user, payload.token);
        currentUserId = payload.user.id;

        const channels = await client.queryChannels(
          {
            type: payload.channel.type,
            members: { $in: [payload.user.id] },
          },
          { last_message_at: -1 },
          { watch: true, state: true, limit: 30 }
        );

        await renderChannelList(channels);

        if (listSubscription) {
          listSubscription.unsubscribe();
        }
        listSubscription = client.on('notification.added_to_channel', async () => {
          const refreshed = await client.queryChannels(
            {
              type: payload.channel.type,
              members: { $in: [payload.user.id] },
            },
            { last_message_at: -1 },
            { watch: true, state: true, limit: 30 }
          );
          await renderChannelList(refreshed);
        });

        formEl?.addEventListener('submit', async (event) => {
          event.preventDefault();
          if (!activeChannel) return;

          const text = inputEl?.value?.trim() ?? '';
          if (!text) return;

          inputEl.value = '';
          await activeChannel.sendMessage({
            text,
          });
        });

        fileEl?.addEventListener('change', async () => {
          if (!activeChannel) return;
          const file = fileEl.files?.[0] ?? null;
          if (!file) return;

          const localUploadId = `upload_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
          const uploadingNode = createUploadingNode();
          messagesEl?.appendChild(uploadingNode);
          messagesEl.scrollTop = messagesEl.scrollHeight;
          pendingUploads.set(localUploadId, uploadingNode);

          try {
            const uploadResponse = await activeChannel.sendImage(file);
            const imageUrl = uploadResponse.file;

            await activeChannel.sendMessage({
              local_upload_id: localUploadId,
              attachments: [{
                type: 'image',
                image_url: imageUrl,
                thumb_url: imageUrl,
                asset_url: imageUrl,
                title: file.name,
              }],
            });
          } catch (_) {
            const failedNode = pendingUploads.get(localUploadId);
            if (failedNode) {
              failedNode.innerHTML = '';
              const content = document.createElement('div');
              content.className = 'max-w-[80%]';

              const sender = document.createElement('div');
              sender.className = 'mb-1 text-right text-xs text-[rgb(var(--theme-primary))]';
              sender.textContent = '我:';
              content.appendChild(sender);

              const failedBlock = document.createElement('div');
              failedBlock.className = 'h-40 w-40 max-w-full rounded-lg border border-[rgb(var(--theme-rose))]/40 bg-[rgb(var(--theme-rose))]/10';
              content.appendChild(failedBlock);

              const failedHint = document.createElement('div');
              failedHint.className = 'mt-1 text-right text-xs text-[rgb(var(--theme-rose))]';
              failedHint.textContent = '图片发送失败，请重试。';
              content.appendChild(failedHint);

              failedNode.appendChild(content);
            }
            pendingUploads.delete(localUploadId);
          } finally {
            fileEl.value = '';
          }
        });

        if (!activeChannel) {
          setStatus('等待新的访客会话...');
        }
      };

      connectAgent().catch((error) => {
        setStatus(error.message || '客服工作台连接失败。');
      });
    </script>
  @endif
</body>
</html>
