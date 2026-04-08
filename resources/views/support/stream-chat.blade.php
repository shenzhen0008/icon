<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Stream Chat | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-hidden bg-theme text-theme md:overflow-auto">
  <x-nav.top />

  <main class="fixed inset-x-0 top-[4.25rem] bottom-[calc(4.25rem+env(safe-area-inset-bottom))] w-full overflow-hidden md:static md:mx-auto md:w-full md:max-w-6xl md:px-6 md:pb-10 md:pt-8">
    @if ($streamEnabled)
      <section class="flex h-full flex-col overflow-hidden rounded-none border border-[rgb(var(--theme-primary))]/20 bg-theme-card shadow-none md:rounded-2xl md:shadow-xl md:shadow-[rgb(var(--theme-primary))]/10">
        <div id="stream-chat-status" class="border-b border-theme px-4 py-3 text-xs text-theme-secondary">正在连接客服...</div>
        <div id="stream-chat-messages" class="min-h-0 flex-1 overflow-y-auto px-4 py-4"></div>
        <form id="stream-chat-form" class="shrink-0 border-t border-theme p-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] md:pb-3">
          <div class="flex items-center gap-2">
            <label for="stream-chat-file" class="inline-flex shrink-0 cursor-pointer items-center whitespace-nowrap rounded-lg border border-theme px-3 py-2 text-xs text-theme-secondary transition hover:border-[rgb(var(--theme-primary))] hover:text-[rgb(var(--theme-primary))]">图片</label>
            <input id="stream-chat-file" type="file" accept="image/*" class="hidden">
            <input
              id="stream-chat-input"
              type="text"
              maxlength="500"
              placeholder="请输入消息..."
              class="w-full rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-sm text-theme placeholder:text-theme-secondary focus:border-[rgb(var(--theme-primary))] focus:outline-none"
            >
            <button
              type="submit"
              class="whitespace-nowrap rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2 text-sm font-semibold text-theme-on-primary transition hover:bg-[rgb(var(--theme-primary))]/80"
            >发送</button>
          </div>
        </form>
      </section>

      <aside id="stream-chat-sound-prompt" class="fixed right-3 top-20 z-40 hidden w-56 rounded-xl border border-[rgb(var(--theme-primary))]/30 bg-theme-card p-3 shadow-lg shadow-[rgb(var(--theme-primary))]/20 md:right-6 md:top-24">
        <p class="text-xs text-theme-secondary">开启消息提醒音？收到新消息时会播放提示音。</p>
        <div class="mt-2 flex items-center justify-end gap-2">
          <button id="stream-chat-sound-dismiss" type="button" class="rounded-lg border border-theme px-2.5 py-1 text-xs text-theme-secondary hover:border-[rgb(var(--theme-primary))]">稍后</button>
          <button id="stream-chat-sound-enable" type="button" class="rounded-lg bg-[rgb(var(--theme-primary))] px-2.5 py-1 text-xs font-semibold text-theme-on-primary hover:bg-[rgb(var(--theme-primary))]/80">开启</button>
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

      const statusEl = document.getElementById('stream-chat-status');
      const messagesEl = document.getElementById('stream-chat-messages');
      const formEl = document.getElementById('stream-chat-form');
      const inputEl = document.getElementById('stream-chat-input');
      const fileEl = document.getElementById('stream-chat-file');
      const soundPromptEl = document.getElementById('stream-chat-sound-prompt');
      const soundEnableEl = document.getElementById('stream-chat-sound-enable');
      const soundDismissEl = document.getElementById('stream-chat-sound-dismiss');
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
      const pendingUploads = new Map();
      let currentUserId = '';
      let soundEnabled = localStorage.getItem('stream_chat_sound_enabled') === '1';
      const audioCtx = window.AudioContext ? new window.AudioContext() : null;

      const setStatus = (message) => {
        if (statusEl) statusEl.textContent = message;
      };

      const clearUnreadBadge = () => {
        localStorage.setItem('stream_chat_unread_count', '0');
        window.dispatchEvent(new CustomEvent('stream-chat-unread-updated', {
          detail: { count: 0 },
        }));
      };
      clearUnreadBadge();

      const buildMessageNode = (message) => {
        const isSelf = message.user?.id === currentUserId;
        const displayName = isSelf
          ? '我'
          : (message.user?.name || (message.user?.id?.startsWith('support_') ? '客服008' : '客服'));

        const wrapper = document.createElement('div');
        wrapper.className = `mb-3 flex ${isSelf ? 'justify-end' : 'justify-start'}`;

        const container = document.createElement('div');
        container.className = 'max-w-[82%]';

        const sender = document.createElement('div');
        sender.className = `mb-1 text-xs ${isSelf ? 'text-right text-[rgb(var(--theme-primary))]' : 'text-theme-secondary'}`;
        sender.textContent = `${displayName}:`;
        container.appendChild(sender);

        if (message.text) {
          const wrapper = document.createElement('div');
          wrapper.className = isSelf
            ? 'rounded-lg bg-[rgb(var(--theme-primary))]/20 px-3 py-2 text-sm text-theme'
            : 'rounded-lg bg-theme-secondary/80 px-3 py-2 text-sm text-theme';
          wrapper.textContent = message.text;
          container.appendChild(wrapper);
        }

        const attachments = Array.isArray(message.attachments) ? message.attachments : [];
        attachments.forEach((attachment) => {
          const imageUrl = attachment?.image_url || attachment?.thumb_url || attachment?.asset_url;
          if (attachment?.type !== 'image' || !imageUrl) return;

          const image = document.createElement('img');
          image.src = imageUrl;
          image.alt = attachment?.title || 'uploaded image';
          image.className = 'mt-2 max-h-64 rounded-lg border border-theme object-contain';
          container.appendChild(image);
        });

        wrapper.appendChild(container);
        return wrapper;
      };

      const renderMessage = (message) => {
        if (!messagesEl || !message) return;
        const node = buildMessageNode(message);
        if (node.childElementCount > 0) messagesEl.appendChild(node);
      };

      const createUploadingNode = () => {
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-3 flex justify-end';
        wrapper.dataset.uploading = '1';

        const container = document.createElement('div');
        container.className = 'max-w-[82%]';

        const sender = document.createElement('div');
        sender.className = 'mb-1 text-right text-xs text-[rgb(var(--theme-primary))]';
        sender.textContent = '我:';
        container.appendChild(sender);

        const skeleton = document.createElement('div');
        skeleton.className = 'h-40 w-40 max-w-full animate-pulse rounded-lg border border-theme bg-theme-secondary/60 blur-[1px]';
        container.appendChild(skeleton);

        const hint = document.createElement('div');
        hint.className = 'mt-1 text-right text-xs text-theme-secondary';
        hint.textContent = '图片上传中...';
        container.appendChild(hint);

        wrapper.appendChild(container);
        return wrapper;
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
        localStorage.setItem('stream_chat_sound_enabled', '1');
        try {
          if ('Notification' in window && Notification.permission === 'default') {
            await Notification.requestPermission();
          }
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

      const connectChat = async () => {
        const response = await fetch('/stream-chat/guest-token', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
          body: JSON.stringify({}),
        });

        if (!response.ok) {
          throw new Error('无法获取客服会话令牌，请稍后再试。');
        }

        const payload = await response.json();
        const client = StreamChat.getInstance(payload.apiKey);
        currentUserId = payload.user.id;

        await client.connectUser(payload.user, payload.token);

        const channel = client.channel(payload.channel.type, payload.channel.id, {
          name: payload.channel.name,
          members: payload.channel.members,
        });

        await channel.watch();
        renderHistory(channel.state.messages);

        channel.on('message.new', (event) => {
          const localUploadId = event.message?.local_upload_id;
          if (localUploadId && pendingUploads.has(localUploadId) && messagesEl) {
            const oldNode = pendingUploads.get(localUploadId);
            const newNode = buildMessageNode(event.message);
            if (newNode.childElementCount > 0) {
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
            clearUnreadBadge();
            beep().catch(() => {});
          }
        });

        fileEl?.addEventListener('change', async () => {
          const file = fileEl.files?.[0] ?? null;
          if (!file) return;

          const localUploadId = `upload_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
          const uploadingNode = createUploadingNode();
          messagesEl?.appendChild(uploadingNode);
          messagesEl.scrollTop = messagesEl.scrollHeight;
          pendingUploads.set(localUploadId, uploadingNode);

          try {
            const uploadResponse = await channel.sendImage(file);
            const imageUrl = uploadResponse.file;

            await channel.sendMessage({
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
              content.className = 'max-w-[82%]';

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

        formEl?.addEventListener('submit', async (event) => {
          event.preventDefault();
          const text = inputEl?.value?.trim() ?? '';
          if (!text) return;

          inputEl.value = '';
          await channel.sendMessage({
            text,
          });
        });

        setStatus('客服已连接，可直接发送消息。');
      };

      connectChat().catch((error) => {
        setStatus(error.message || '客服连接失败，请稍后重试。');
      });
    </script>
  @endif
</body>
</html>
