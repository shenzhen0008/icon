export const STREAM_CHAT_UNREAD_COUNT_KEY = 'stream_chat_unread_count';
export const STREAM_CHAT_SESSION_KEY = 'stream_chat_has_session';

export const parseStoredUnreadCount = (value) => {
  const count = Number.parseInt(String(value ?? '0'), 10);

  return Number.isFinite(count) && count > 0 ? count : 0;
};

export const syncUnreadDots = (dots, count) => {
  const hasUnread = count > 0;

  dots.forEach((dot) => {
    dot.classList.toggle('hidden', !hasUnread);
    dot.setAttribute('aria-hidden', hasUnread ? 'false' : 'true');
  });
};

const resolvePathname = (windowObj) => windowObj?.location?.pathname ?? '';

const shouldCheckRemoteUnread = (storage, count) => (
  count > 0 || storage?.getItem?.(STREAM_CHAT_SESSION_KEY) === '1'
);

const persistUnreadCount = (storage, count) => {
  storage?.setItem?.(STREAM_CHAT_UNREAD_COUNT_KEY, String(Math.max(0, count)));
};

export const initStreamChatUnreadBadge = async ({
  root = document,
  storage = window.localStorage,
  windowObj = window,
  fetchFn = window.fetch.bind(window),
  streamImporter = () => import('https://cdn.jsdelivr.net/npm/stream-chat/+esm'),
} = {}) => {
  if (!root || typeof root.querySelectorAll !== 'function') {
    return null;
  }

  const dots = Array.from(root.querySelectorAll('[data-stream-chat-unread-dot]'));
  if (dots.length === 0) {
    return null;
  }

  const cachedCount = parseStoredUnreadCount(storage?.getItem?.(STREAM_CHAT_UNREAD_COUNT_KEY));
  syncUnreadDots(dots, cachedCount);

  const applyUnreadCount = (nextCount) => {
    persistUnreadCount(storage, nextCount);
    syncUnreadDots(dots, nextCount);
  };

  windowObj?.addEventListener?.('stream-chat-unread-updated', (event) => {
    const nextCount = parseStoredUnreadCount(event?.detail?.count);
    applyUnreadCount(nextCount);
  });

  if (resolvePathname(windowObj) === '/stream-chat' || !shouldCheckRemoteUnread(storage, cachedCount)) {
    return null;
  }

  try {
    const tokenResponse = await fetchFn('/stream-chat/notify-token', {
      headers: {
        Accept: 'application/json',
      },
    });

    if (!tokenResponse.ok) {
      if (tokenResponse.status === 404) {
        storage?.removeItem?.(STREAM_CHAT_SESSION_KEY);
        applyUnreadCount(0);
      }

      return null;
    }

    const payload = await tokenResponse.json();
    const { StreamChat } = await streamImporter();
    const client = StreamChat.getInstance(payload.apiKey);
    const connectedUser = await client.connectUser(payload.user, payload.token);
    const initialCount = parseStoredUnreadCount(connectedUser?.me?.total_unread_count);

    storage?.setItem?.(STREAM_CHAT_SESSION_KEY, '1');
    applyUnreadCount(initialCount);

    client.on((event) => {
      if (event?.total_unread_count === undefined) {
        return;
      }

      const nextCount = parseStoredUnreadCount(event.total_unread_count);
      applyUnreadCount(nextCount);
    });

    return client;
  } catch (_) {
    syncUnreadDots(dots, cachedCount);

    return null;
  }
};
