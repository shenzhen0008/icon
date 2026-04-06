<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Stream Chat | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-hidden bg-slate-950 text-slate-100 md:overflow-auto">
  <x-nav.top />

  <main class="fixed inset-x-0 top-14 bottom-11 w-full overflow-hidden md:static md:mx-auto md:w-full md:max-w-6xl md:px-6 md:pb-10 md:pt-8">
    @if ($streamEnabled)
      <section class="flex h-full flex-col overflow-hidden rounded-none border border-cyan-400/20 bg-slate-900/70 shadow-none md:rounded-2xl md:shadow-xl md:shadow-cyan-500/10">
        <div id="stream-chat-status" class="border-b border-white/10 px-4 py-3 text-xs text-slate-400">正在连接客服...</div>
        <div id="stream-chat-messages" class="min-h-0 flex-1 overflow-y-auto px-4 py-4"></div>
        <form id="stream-chat-form" class="shrink-0 border-t border-white/10 p-3">
          <div class="flex items-center gap-2">
            <label for="stream-chat-file" class="inline-flex cursor-pointer items-center rounded-lg border border-white/15 px-3 py-2 text-xs text-slate-300 transition hover:border-cyan-300 hover:text-cyan-200">图片</label>
            <input id="stream-chat-file" type="file" accept="image/*" class="hidden">
            <input
              id="stream-chat-input"
              type="text"
              maxlength="500"
              placeholder="请输入消息..."
              class="w-full rounded-lg border border-white/15 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-300 focus:outline-none"
            >
            <button
              type="submit"
              class="rounded-lg bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300"
            >发送</button>
          </div>
        </form>
      </section>

      <aside id="stream-chat-sound-prompt" class="fixed right-3 top-20 z-40 hidden w-56 rounded-xl border border-cyan-400/30 bg-slate-900/95 p-3 shadow-lg shadow-cyan-500/20 md:right-6 md:top-24">
        <p class="text-xs text-slate-300">开启消息提醒音？收到新消息时会播放提示音。</p>
        <div class="mt-2 flex items-center justify-end gap-2">
          <button id="stream-chat-sound-dismiss" type="button" class="rounded-lg border border-white/15 px-2.5 py-1 text-xs text-slate-300 hover:border-white/30">稍后</button>
          <button id="stream-chat-sound-enable" type="button" class="rounded-lg bg-cyan-400 px-2.5 py-1 text-xs font-semibold text-slate-950 hover:bg-cyan-300">开启</button>
        </div>
      </aside>
    @else
      <section class="rounded-2xl border border-dashed border-white/20 bg-slate-900/60 p-8 text-sm text-slate-300">
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
        sender.className = `mb-1 text-xs ${isSelf ? 'text-right text-cyan-200' : 'text-slate-400'}`;
        sender.textContent = `${displayName}:`;
        container.appendChild(sender);

        if (message.text) {
          const wrapper = document.createElement('div');
          wrapper.className = isSelf
            ? 'rounded-lg bg-cyan-500/30 px-3 py-2 text-sm text-cyan-100'
            : 'rounded-lg bg-slate-800/80 px-3 py-2 text-sm text-slate-100';
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
          image.className = 'mt-2 max-h-64 rounded-lg border border-white/10 object-contain';
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
        sender.className = 'mb-1 text-right text-xs text-cyan-200';
        sender.textContent = '我:';
        container.appendChild(sender);

        const skeleton = document.createElement('div');
        skeleton.className = 'h-40 w-40 max-w-full animate-pulse rounded-lg border border-white/10 bg-slate-700/60 blur-[1px]';
        container.appendChild(skeleton);

        const hint = document.createElement('div');
        hint.className = 'mt-1 text-right text-xs text-slate-400';
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
              sender.className = 'mb-1 text-right text-xs text-cyan-200';
              sender.textContent = '我:';
              content.appendChild(sender);

              const failedBlock = document.createElement('div');
              failedBlock.className = 'h-40 w-40 max-w-full rounded-lg border border-rose-400/40 bg-rose-500/10';
              content.appendChild(failedBlock);

              const failedHint = document.createElement('div');
              failedHint.className = 'mt-1 text-right text-xs text-rose-300';
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
