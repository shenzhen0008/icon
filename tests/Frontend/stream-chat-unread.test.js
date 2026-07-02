import test from 'node:test';
import assert from 'node:assert/strict';

import {
  initStreamChatUnreadBadge,
  parseStoredUnreadCount,
  syncUnreadDots,
} from '../../resources/js/stream-chat-unread.js';

const createClassList = (initial = []) => {
  const values = new Set(initial);

  return {
    add(className) {
      values.add(className);
    },
    remove(className) {
      values.delete(className);
    },
    toggle(className, force) {
      if (force) {
        values.add(className);
        return true;
      }

      values.delete(className);
      return false;
    },
    contains(className) {
      return values.has(className);
    },
  };
};

const createDot = () => ({
  classList: createClassList(['hidden']),
  attributes: {},
  setAttribute(name, value) {
    this.attributes[name] = value;
  },
});

const createStorage = (initial = {}) => {
  const values = { ...initial };

  return {
    getItem(key) {
      return Object.prototype.hasOwnProperty.call(values, key) ? values[key] : null;
    },
    setItem(key, value) {
      values[key] = String(value);
    },
    removeItem(key) {
      delete values[key];
    },
    values,
  };
};

const createRoot = (dots) => ({
  querySelectorAll(selector) {
    return selector === '[data-stream-chat-unread-dot]' ? dots : [];
  },
});

const createWindow = (pathname = '/') => {
  const listeners = {};

  return {
    location: { pathname },
    addEventListener(type, listener) {
      listeners[type] = listener;
    },
    dispatch(type, event) {
      return listeners[type]?.(event);
    },
  };
};

test('parseStoredUnreadCount handles positive and invalid values', () => {
  assert.equal(parseStoredUnreadCount('2'), 2);
  assert.equal(parseStoredUnreadCount('0'), 0);
  assert.equal(parseStoredUnreadCount('-1'), 0);
  assert.equal(parseStoredUnreadCount('abc'), 0);
  assert.equal(parseStoredUnreadCount(null), 0);
});

test('syncUnreadDots toggles hidden class and aria-hidden state', () => {
  const dot = createDot();

  syncUnreadDots([dot], 3);
  assert.equal(dot.classList.contains('hidden'), false);
  assert.equal(dot.attributes['aria-hidden'], 'false');

  syncUnreadDots([dot], 0);
  assert.equal(dot.classList.contains('hidden'), true);
  assert.equal(dot.attributes['aria-hidden'], 'true');
});

test('initStreamChatUnreadBadge shows cached unread count before remote check resolves', async () => {
  const dot = createDot();
  const storage = createStorage({ stream_chat_unread_count: '4' });
  let fetched = false;
  let resolveFetch;

  const initPromise = initStreamChatUnreadBadge({
    root: createRoot([dot]),
    storage,
    windowObj: createWindow('/'),
    fetchFn: async () => {
      fetched = true;
      return new Promise((resolve) => {
        resolveFetch = () => resolve({ ok: false, status: 404 });
      });
    },
  });

  assert.equal(dot.classList.contains('hidden'), false);
  assert.equal(fetched, true);

  resolveFetch();
  await initPromise;

  assert.equal(dot.classList.contains('hidden'), true);
});

test('initStreamChatUnreadBadge skips remote check for visitors without chat session marker', async () => {
  const dot = createDot();
  const storage = createStorage();
  let fetched = false;

  await initStreamChatUnreadBadge({
    root: createRoot([dot]),
    storage,
    windowObj: createWindow('/'),
    fetchFn: async () => {
      fetched = true;
      return { ok: false, status: 404 };
    },
  });

  assert.equal(dot.classList.contains('hidden'), true);
  assert.equal(fetched, false);
});

test('initStreamChatUnreadBadge uses notify token and Stream unread events for existing sessions', async () => {
  const dot = createDot();
  const storage = createStorage({ stream_chat_has_session: '1' });
  const fetchRequests = [];
  let eventListener = null;
  const client = {
    connectUser: async () => ({ me: { total_unread_count: 2 } }),
    on(listener) {
      eventListener = listener;
    },
  };

  await initStreamChatUnreadBadge({
    root: createRoot([dot]),
    storage,
    windowObj: createWindow('/'),
    fetchFn: async (url) => {
      fetchRequests.push(url);
      return {
        ok: true,
        json: async () => ({
          apiKey: 'key',
          token: 'token',
          user: { id: 'guest_1', name: 'guest' },
        }),
      };
    },
    streamImporter: async () => ({
      StreamChat: {
        getInstance: () => client,
      },
    }),
  });

  assert.deepEqual(fetchRequests, ['/stream-chat/notify-token']);
  assert.equal(storage.values.stream_chat_unread_count, '2');
  assert.equal(dot.classList.contains('hidden'), false);

  eventListener({ total_unread_count: 0 });
  assert.equal(storage.values.stream_chat_unread_count, '0');
  assert.equal(dot.classList.contains('hidden'), true);
});

test('initStreamChatUnreadBadge does not run sound hooks when unread count increases', async () => {
  const dot = createDot();
  const storage = createStorage({ stream_chat_has_session: '1' });
  let eventListener = null;
  let soundCount = 0;
  const client = {
    connectUser: async () => ({ me: { total_unread_count: 0 } }),
    on(listener) {
      eventListener = listener;
    },
  };

  await initStreamChatUnreadBadge({
    root: createRoot([dot]),
    storage,
    windowObj: createWindow('/'),
    fetchFn: async () => ({
      ok: true,
      json: async () => ({
        apiKey: 'key',
        token: 'token',
        user: { id: 'guest_1', name: 'guest' },
      }),
    }),
    streamImporter: async () => ({
      StreamChat: {
        getInstance: () => client,
      },
    }),
    playUnreadSound: () => {
      soundCount += 1;
    },
  });

  eventListener({ total_unread_count: 1 });
  eventListener({ total_unread_count: 1 });
  eventListener({ total_unread_count: 0 });
  eventListener({ total_unread_count: 2 });

  assert.equal(soundCount, 0);
});

test('initStreamChatUnreadBadge does not run sound hooks for custom unread events', async () => {
  const dot = createDot();
  const storage = createStorage();
  const windowObj = createWindow('/');
  let soundCount = 0;

  await initStreamChatUnreadBadge({
    root: createRoot([dot]),
    storage,
    windowObj,
    fetchFn: async () => ({ ok: false, status: 404 }),
    playUnreadSound: () => {
      soundCount += 1;
    },
  });

  windowObj.dispatch('stream-chat-unread-updated', { detail: { count: 1 } });
  windowObj.dispatch('stream-chat-unread-updated', { detail: { count: 1 } });
  windowObj.dispatch('stream-chat-unread-updated', { detail: { count: 0 } });
  windowObj.dispatch('stream-chat-unread-updated', { detail: { count: 3 } });

  assert.equal(soundCount, 0);
});

test('initStreamChatUnreadBadge skips remote check on stream chat page', async () => {
  const dot = createDot();
  const storage = createStorage({ stream_chat_has_session: '1' });
  let fetched = false;

  await initStreamChatUnreadBadge({
    root: createRoot([dot]),
    storage,
    windowObj: createWindow('/stream-chat'),
    fetchFn: async () => {
      fetched = true;
      return { ok: true };
    },
  });

  assert.equal(fetched, false);
});
