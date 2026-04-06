<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Stream Chat Agent | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
  <x-nav.top />

  <main class="mx-auto w-full max-w-6xl px-6 pb-28 pt-8 md:pb-10">
    @if ($streamEnabled)
      <section class="grid overflow-hidden rounded-2xl border border-cyan-400/20 bg-slate-900/70 shadow-xl shadow-cyan-500/10 md:grid-cols-[17rem_1fr]">
        <aside class="border-r border-white/10">
          <div class="border-b border-white/10 px-4 py-3 text-xs text-slate-400">会话列表</div>
          <div id="agent-channel-list" class="h-[calc(100vh-19rem)] min-h-[360px] overflow-y-auto"></div>
        </aside>
        <div class="flex min-h-[420px] flex-col">
          <div id="agent-status" class="border-b border-white/10 px-4 py-3 text-xs text-slate-400">正在连接客服工作台...</div>
          <div id="agent-messages" class="h-[calc(100vh-24rem)] min-h-[260px] overflow-y-auto px-4 py-4"></div>
          <form id="agent-chat-form" class="border-t border-white/10 p-3">
            <div class="flex items-center gap-2">
              <label for="agent-chat-file" class="inline-flex cursor-pointer items-center rounded-lg border border-white/15 px-3 py-2 text-xs text-slate-300 transition hover:border-cyan-300 hover:text-cyan-200">图片</label>
              <input id="agent-chat-file" type="file" accept="image/*" class="hidden">
              <input
                id="agent-chat-input"
                type="text"
                maxlength="500"
                placeholder="输入回复内容..."
                class="w-full rounded-lg border border-white/15 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-300 focus:outline-none"
              >
              <button
                type="submit"
                class="rounded-lg bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300"
              >发送</button>
            </div>
          </form>
        </div>
      </section>

      <aside id="agent-chat-sound-prompt" class="fixed right-3 top-20 z-40 hidden w-56 rounded-xl border border-cyan-400/30 bg-slate-900/95 p-3 shadow-lg shadow-cyan-500/20 md:right-6 md:top-24">
        <p class="text-xs text-slate-300">开启消息提醒音？收到访客新消息时会播放提示音。</p>
        <div class="mt-2 flex items-center justify-end gap-2">
          <button id="agent-chat-sound-dismiss" type="button" class="rounded-lg border border-white/15 px-2.5 py-1 text-xs text-slate-300 hover:border-white/30">稍后</button>
          <button id="agent-chat-sound-enable" type="button" class="rounded-lg bg-cyan-400 px-2.5 py-1 text-xs font-semibold text-slate-950 hover:bg-cyan-300">开启</button>
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

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
      const statusEl = document.getElementById('agent-status');
      const listEl = document.getElementById('agent-channel-list');
      const messagesEl = document.getElementById('agent-messages');
      const formEl = document.getElementById('agent-chat-form');
      const inputEl = document.getElementById('agent-chat-input');
      const fileEl = document.getElementById('agent-chat-file');
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

      const setStatus = (message) => {
        if (statusEl) statusEl.textContent = message;
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
        sender.className = `mb-1 text-xs ${isAgent ? 'text-right text-cyan-200' : 'text-slate-400'}`;
        sender.textContent = `${displayName}:`;
        content.appendChild(sender);

        if (message.text) {
          const bubble = document.createElement('div');
          bubble.className = isAgent
            ? 'rounded-lg bg-cyan-500/30 px-3 py-2 text-sm text-cyan-100'
            : 'rounded-lg bg-slate-800/80 px-3 py-2 text-sm text-slate-100';
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
          image.className = 'mt-2 max-h-64 rounded-lg border border-white/10 object-contain';
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
        sender.className = 'mb-1 text-right text-xs text-cyan-200';
        sender.textContent = '我:';
        content.appendChild(sender);

        const skeleton = document.createElement('div');
        skeleton.className = 'h-40 w-40 max-w-full animate-pulse rounded-lg border border-cyan-300/20 bg-cyan-500/10 blur-[1px]';
        content.appendChild(skeleton);

        const hint = document.createElement('div');
        hint.className = 'mt-1 text-right text-xs text-cyan-200';
        hint.textContent = '图片上传中...';
        content.appendChild(hint);

        wrapper.appendChild(content);
        return wrapper;
      };

      const activateChannel = async (channel, element) => {
        if (!channel) return;
        if (activeSubscription) {
          activeSubscription.unsubscribe();
          activeSubscription = null;
        }

        activeChannel = channel;
        await channel.watch();
        renderHistory(channel.state.messages);

        document.querySelectorAll('[data-agent-channel-item]').forEach((node) => {
          node.classList.remove('bg-cyan-500/20', 'text-cyan-200');
          node.classList.add('text-slate-200');
        });

        if (element) {
          element.classList.add('bg-cyan-500/20', 'text-cyan-200');
          element.classList.remove('text-slate-200');
        }

        setStatus(`当前会话：${channel.data?.name || channel.id}`);

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

      const renderChannelList = async (channels) => {
        if (!listEl) return;
        listEl.innerHTML = '';

        if (channels.length === 0) {
          const empty = document.createElement('div');
          empty.className = 'px-4 py-4 text-sm text-slate-400';
          empty.textContent = '暂无访客会话。';
          listEl.appendChild(empty);
          resetMessages();
          setStatus('等待新的访客消息...');
          return;
        }

        channels.forEach((channel, index) => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.dataset.agentChannelItem = '1';
          btn.className = 'block w-full border-b border-white/5 px-4 py-3 text-left text-sm text-slate-200 hover:bg-white/5';
          btn.textContent = channel.data?.name || channel.id;
          btn.addEventListener('click', () => {
            activateChannel(channel, btn);
          });
          listEl.appendChild(btn);

          if (index === 0 && !activeChannel) {
            activateChannel(channel, btn);
          }
        });
      };

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
          activeChannel = null;
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
