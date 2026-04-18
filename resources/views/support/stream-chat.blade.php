<!doctype html>
<html lang="{{ __('pages/stream-chat.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ __('pages/stream-chat.meta_title') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden overflow-y-hidden bg-theme text-theme md:overflow-auto">
  <x-layout.background-glow />
  <x-nav.top />

  <main
    class="fixed inset-x-0 w-full overflow-hidden md:static md:mx-auto md:w-full md:max-w-4xl md:px-6 md:pb-10 md:pt-8"
    style="top: var(--top-nav-height, 4.25rem); bottom: max(calc(var(--mobile-nav-height, 4.25rem) + env(safe-area-inset-bottom)), var(--chat-keyboard-inset, 0px));"
  >
    @if ($streamEnabled)
      <section class="flex h-full flex-col overflow-hidden rounded-none border border-[rgb(var(--theme-primary))]/20 bg-theme-card shadow-none md:rounded-2xl md:shadow-xl md:shadow-[rgb(var(--theme-primary))]/10">
        <div id="stream-chat-status" class="border-b border-theme px-4 py-3 text-scale-micro text-theme-secondary">{{ __('pages/stream-chat.status_connecting') }}</div>
        <div id="stream-chat-messages" class="min-h-0 flex-1 overflow-y-auto px-4 py-4"></div>
        <form id="stream-chat-form" class="shrink-0 border-t border-theme p-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] md:pb-3">
          <div class="flex min-w-0 items-center gap-2">
            <label for="stream-chat-file" class="inline-flex shrink-0 cursor-pointer items-center whitespace-nowrap rounded-lg border border-theme px-3 py-2 text-scale-micro text-theme-secondary transition hover:border-[rgb(var(--theme-primary))] hover:text-[rgb(var(--theme-primary))]">{{ __('pages/stream-chat.label_image') }}</label>
            <input id="stream-chat-file" type="file" accept="image/*" class="hidden">
            <input
              id="stream-chat-input"
              type="text"
              maxlength="500"
              placeholder="{{ __('pages/stream-chat.input_placeholder') }}"
              class="flex-1 min-w-0 rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-scale-body text-theme placeholder:text-theme-secondary focus:border-[rgb(var(--theme-primary))] focus:outline-none text-scale-body"
            >
            <button
              type="submit"
              class="text-scale-ui whitespace-nowrap rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2 font-semibold text-theme-on-primary transition hover:bg-[rgb(var(--theme-primary))]/80"
            >{{ __('pages/stream-chat.button_send') }}</button>
          </div>
        </form>
      </section>

      <aside id="stream-chat-sound-prompt" class="fixed right-3 top-20 z-40 hidden w-56 rounded-xl border border-[rgb(var(--theme-primary))]/30 bg-theme-card p-3 shadow-lg shadow-[rgb(var(--theme-primary))]/20 md:right-6 md:top-24">
        <p class="text-scale-micro text-theme-secondary">{{ __('pages/stream-chat.sound_prompt') }}</p>
        <div class="mt-2 flex items-center justify-end gap-2">
          <button id="stream-chat-sound-dismiss" type="button" class="rounded-lg border border-theme px-2.5 py-1 text-scale-micro text-theme-secondary hover:border-[rgb(var(--theme-primary))]">{{ __('pages/stream-chat.sound_later') }}</button>
          <button id="stream-chat-sound-enable" type="button" class="rounded-lg bg-[rgb(var(--theme-primary))] px-2.5 py-1 text-scale-micro font-semibold text-theme-on-primary hover:bg-[rgb(var(--theme-primary))]/80">{{ __('pages/stream-chat.sound_enable') }}</button>
        </div>
      </aside>
    @else
      <section class="rounded-2xl border border-dashed border-theme bg-theme-card p-8 text-scale-body text-theme-secondary">
        {{ __('pages/stream-chat.not_configured') }}
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
      const streamNotifyBootstrapKey = 'stream_chat_notify_bootstrap_ready';
      const i18n = {
        nameSelf: @json(__('pages/stream-chat.name_self')),
        nameSupportDefault: @json(__('pages/stream-chat.name_support_default')),
        nameSupportFallback: @json(__('pages/stream-chat.name_support_fallback')),
        imageAltUploaded: @json(__('pages/stream-chat.image_alt_uploaded')),
        labelSelfPrefix: @json(__('pages/stream-chat.label_self_prefix')),
        hintImageUploading: @json(__('pages/stream-chat.hint_image_uploading')),
        statusSoundEnabled: @json(__('pages/stream-chat.status_sound_enabled')),
        statusSoundEnableFailed: @json(__('pages/stream-chat.status_sound_enable_failed')),
        errorTokenFailed: @json(__('pages/stream-chat.error_token_failed')),
        hintImageSendFailed: @json(__('pages/stream-chat.hint_image_send_failed')),
        statusConnected: @json(__('pages/stream-chat.status_connected')),
        statusConnectFailed: @json(__('pages/stream-chat.status_connect_failed')),
      };
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
          ? i18n.nameSelf
          : (message.user?.name || (message.user?.id?.startsWith('support_') ? i18n.nameSupportFallback : i18n.nameSupportDefault));

        const wrapper = document.createElement('div');
        wrapper.className = `mb-3 flex ${isSelf ? 'justify-end' : 'justify-start'}`;

        const container = document.createElement('div');
        container.className = 'max-w-[82%]';

        const sender = document.createElement('div');
        sender.className = `mb-1 text-scale-micro ${isSelf ? 'text-right text-[rgb(var(--theme-primary))]' : 'text-theme-secondary'}`;
        sender.textContent = `${displayName}:`;
        container.appendChild(sender);

        if (message.text) {
          const wrapper = document.createElement('div');
          wrapper.className = isSelf
            ? 'rounded-lg bg-[rgb(var(--theme-primary))]/20 px-3 py-2 text-scale-body text-theme'
            : 'rounded-lg bg-theme-secondary/80 px-3 py-2 text-scale-body text-theme';
          wrapper.textContent = message.text;
          container.appendChild(wrapper);
        }

        const attachments = Array.isArray(message.attachments) ? message.attachments : [];
        attachments.forEach((attachment) => {
          const imageUrl = attachment?.image_url || attachment?.thumb_url || attachment?.asset_url;
          if (attachment?.type !== 'image' || !imageUrl) return;

          const image = document.createElement('img');
          image.src = imageUrl;
          image.alt = attachment?.title || i18n.imageAltUploaded;
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
        sender.className = 'mb-1 text-right text-scale-micro text-[rgb(var(--theme-primary))]';
        sender.textContent = i18n.labelSelfPrefix;
        container.appendChild(sender);

        const skeleton = document.createElement('div');
        skeleton.className = 'h-40 w-40 max-w-full animate-pulse rounded-lg border border-theme bg-theme-secondary/60 blur-[1px]';
        container.appendChild(skeleton);

        const hint = document.createElement('div');
        hint.className = 'mt-1 text-right text-scale-micro text-theme-secondary';
        hint.textContent = i18n.hintImageUploading;
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

        const now = audioCtx.currentTime;
        const tone = (freq, delay, duration, peakGain) => {
          const oscillator = audioCtx.createOscillator();
          const gainNode = audioCtx.createGain();
          oscillator.type = 'triangle';
          oscillator.frequency.setValueAtTime(freq, now + delay);
          gainNode.gain.setValueAtTime(0.0001, now + delay);
          gainNode.gain.exponentialRampToValueAtTime(peakGain, now + delay + 0.02);
          gainNode.gain.exponentialRampToValueAtTime(0.0001, now + delay + duration);
          oscillator.connect(gainNode);
          gainNode.connect(audioCtx.destination);
          oscillator.start(now + delay);
          oscillator.stop(now + delay + duration + 0.02);
        };

        tone(1046, 0, 0.16, 0.09);
        tone(1318, 0.11, 0.2, 0.085);
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
          setStatus(i18n.statusSoundEnabled);
        } catch (_) {
          setStatus(i18n.statusSoundEnableFailed);
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
          throw new Error(i18n.errorTokenFailed);
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
        if ((channel.data?.name || '') !== payload.channel.name) {
          channel.updatePartial({
            set: { name: payload.channel.name },
          }).catch(() => {});
        }
        localStorage.setItem(streamNotifyBootstrapKey, '1');
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
              sender.className = 'mb-1 text-right text-scale-micro text-[rgb(var(--theme-primary))]';
              sender.textContent = i18n.labelSelfPrefix;
              content.appendChild(sender);

              const failedBlock = document.createElement('div');
              failedBlock.className = 'h-40 w-40 max-w-full rounded-lg border border-[rgb(var(--theme-rose))]/40 bg-[rgb(var(--theme-rose))]/10';
              content.appendChild(failedBlock);

              const failedHint = document.createElement('div');
              failedHint.className = 'mt-1 text-right text-scale-micro text-[rgb(var(--theme-rose))]';
              failedHint.textContent = i18n.hintImageSendFailed;
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

        setStatus(i18n.statusConnected);
      };

      connectChat().catch((error) => {
        setStatus(error.message || i18n.statusConnectFailed);
      });
    </script>
  @endif
</body>
</html>
