export const STREAM_CHAT_UNREAD_COUNT_KEY = 'stream_chat_unread_count';
export const STREAM_CHAT_SESSION_KEY = 'stream_chat_has_session';
export const STREAM_CHAT_ACCOUNT_CONTEXT_KEY = 'stream_chat_account_context';

let stopUnreadUpdates = () => {};

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
  accountContext = 'guest',
} = {}) => {
  if (!root || typeof root.querySelectorAll !== 'function') {
    return null;
  }

  const dots = Array.from(root.querySelectorAll('[data-stream-chat-unread-dot]'));
  if (dots.length === 0) {
    return null;
  }

  const disconnectPrevious = stopUnreadUpdates(accountContext);
  const previousContext = storage?.getItem?.(STREAM_CHAT_ACCOUNT_CONTEXT_KEY);
  if (previousContext !== accountContext) {
    storage?.removeItem?.(STREAM_CHAT_UNREAD_COUNT_KEY);
    storage?.removeItem?.(STREAM_CHAT_SESSION_KEY);
  }
  storage?.setItem?.(STREAM_CHAT_ACCOUNT_CONTEXT_KEY, accountContext);
  const isAuthenticated = accountContext.startsWith('user:');
  let active = true;
  let client;
  let subscription;
  let cachedCount = parseStoredUnreadCount(storage?.getItem?.(STREAM_CHAT_UNREAD_COUNT_KEY));
  syncUnreadDots(dots, cachedCount);

  const applyUnreadCount = (nextCount) => {
    if (!active) return;
    persistUnreadCount(storage, nextCount);
    syncUnreadDots(dots, nextCount);
  };

  const onUnreadUpdated = (event) => {
    const nextCount = parseStoredUnreadCount(event?.detail?.count);
    applyUnreadCount(nextCount);
  };
  windowObj?.addEventListener?.('stream-chat-unread-updated', onUnreadUpdated);
  stopUnreadUpdates = (nextContext) => {
    active = false;
    subscription?.unsubscribe();
    windowObj?.removeEventListener?.('stream-chat-unread-updated', onUnreadUpdated);
    return client && nextContext !== accountContext ? client.disconnectUser() : undefined;
  };

  try {
    if (disconnectPrevious) await disconnectPrevious;
    if (!active || resolvePathname(windowObj) === '/stream-chat'
        || (!isAuthenticated && !shouldCheckRemoteUnread(storage, cachedCount))) {
      return null;
    }

    const tokenResponse = await fetchFn('/stream-chat/notify-token', {
      headers: {
        Accept: 'application/json',
      },
    });
    if (!active) return null;

    if (!tokenResponse.ok) {
      if (tokenResponse.status === 404) {
        storage?.removeItem?.(STREAM_CHAT_SESSION_KEY);
        applyUnreadCount(0);
      }

      return null;
    }

    const payload = await tokenResponse.json();
    const { StreamChat } = await streamImporter();
    if (!active) return null;
    client = StreamChat.getInstance(payload.apiKey);
    if (client.userID && client.userID !== payload.user.id) {
      cachedCount = 0;
      storage?.removeItem?.(STREAM_CHAT_SESSION_KEY);
      applyUnreadCount(0);
      await client.disconnectUser();
    }
    const connectedUser = client.userID === payload.user.id
      ? { me: client.user }
      : await client.connectUser(payload.user, payload.token);
    if (!active) return null;
    const initialCount = parseStoredUnreadCount(connectedUser?.me?.total_unread_count);

    storage?.setItem?.(STREAM_CHAT_SESSION_KEY, '1');
    applyUnreadCount(initialCount);

    subscription = client.on((event) => {
      if (event?.total_unread_count === undefined) {
        return;
      }

      const nextCount = parseStoredUnreadCount(event.total_unread_count);
      applyUnreadCount(nextCount);
    });

    return client;
  } catch (_) {
    if (active) syncUnreadDots(dots, cachedCount);

    return null;
  }
};
