import test, { afterEach } from 'node:test';
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
    removeEventListener(type, listener) {
      if (listeners[type] === listener) delete listeners[type];
    },
    dispatch(type, event) {
      return listeners[type]?.(event);
    },
  };
};

afterEach(async () => {
  await initStreamChatUnreadBadge({
    root: createRoot([createDot()]),
    storage: createStorage(),
    windowObj: createWindow('/stream-chat'),
    fetchFn: async () => { throw new Error('unexpected request'); },
  });
});

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
  const storage = createStorage({ stream_chat_account_context: 'guest', stream_chat_unread_count: '4' });
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
  const storage = createStorage({ stream_chat_account_context: 'guest', stream_chat_has_session: '1' });
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
  const storage = createStorage({ stream_chat_account_context: 'guest', stream_chat_has_session: '1' });
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
  const storage = createStorage({ stream_chat_account_context: 'guest', stream_chat_has_session: '1' });
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

const createConnectionScenario = ({
  accountContext = 'user:1', initialStorage = {}, userID, disconnectFails = false,
  connectFails = false, status = 200,
} = {}) => {
  const calls = [];
  const storage = createStorage(initialStorage);
  const dot = createDot();
  const windowObj = createWindow();
  let listener;
  const client = {
    userID,
    user: { total_unread_count: 3 },
    async disconnectUser() {
      calls.push('disconnect');
      if (disconnectFails) throw new Error('disconnect failed');
      this.userID = undefined;
    },
    async connectUser(user) {
      calls.push(`connect:${user.id}`);
      if (connectFails) throw new Error('connect failed');
      this.userID = user.id;
      return { me: { total_unread_count: 2 } };
    },
    on(callback) {
      calls.push('subscribe');
      listener = callback;
      return { unsubscribe() { calls.push('unsubscribe'); } };
    },
  };
  const options = {
    accountContext, storage, windowObj,
    root: createRoot([dot]),
    fetchFn: async () => {
      calls.push('fetch');
      return {
        ok: status === 200, status,
        json: async () => ({ apiKey: 'key', user: { id: 'customer1' }, token: 'token' }),
      };
    },
    streamImporter: async () => ({ StreamChat: { getInstance: () => client } }),
  };
  return { options, client, storage, dot, calls, emit: (event) => listener(event) };
};

test('logged-in users check unread on a new device without a guest marker', async () => {
  const scenario = createConnectionScenario();
  assert.equal(await initStreamChatUnreadBadge(scenario.options), scenario.client);
  assert.deepEqual(scenario.calls, ['fetch', 'connect:customer1', 'subscribe']);
  assert.equal(scenario.storage.values.stream_chat_unread_count, '2');
  assert.equal(scenario.dot.classList.contains('hidden'), false);
});

test('legacy caches without an account context are cleared before display', async () => {
  const scenario = createConnectionScenario({
    accountContext: 'guest', initialStorage: { stream_chat_unread_count: '7', stream_chat_has_session: '1' },
  });
  await initStreamChatUnreadBadge(scenario.options);
  assert.deepEqual(scenario.calls, []);
  assert.equal(scenario.storage.values.stream_chat_unread_count, undefined);
  assert.equal(scenario.storage.values.stream_chat_has_session, undefined);
  assert.equal(scenario.dot.classList.contains('hidden'), true);
});

test('account changes clear old cache before display and still check for logged-in users', async () => {
  for (const [previousContext, accountContext] of [
    ['user:1', 'user:2'], ['user:1', 'guest'], ['guest', 'user:1'],
  ]) {
    const scenario = createConnectionScenario({
      accountContext, status: 503,
      initialStorage: {
        stream_chat_account_context: previousContext,
        stream_chat_has_session: '1', stream_chat_unread_count: '9',
      },
    });
    const result = initStreamChatUnreadBadge(scenario.options);
    assert.equal(scenario.dot.classList.contains('hidden'), true);
    await result;
    assert.equal(scenario.storage.values.stream_chat_unread_count, undefined);
    assert.equal(scenario.storage.values.stream_chat_has_session, undefined);
    assert.equal(scenario.storage.values.stream_chat_account_context, accountContext);
    assert.deepEqual(scenario.calls, accountContext === 'guest' ? [] : ['fetch']);
  }
});

test('same-account request failure retains that account cache', async () => {
  const scenario = createConnectionScenario({
    status: 503,
    initialStorage: { stream_chat_account_context: 'user:1', stream_chat_unread_count: '4' },
  });
  await initStreamChatUnreadBadge(scenario.options);
  assert.equal(scenario.storage.values.stream_chat_unread_count, '4');
  assert.equal(scenario.dot.classList.contains('hidden'), false);
});

test('existing SDK identity is disconnected before connecting the current user', async () => {
  const scenario = createConnectionScenario({ userID: 'old-customer' });
  await initStreamChatUnreadBadge(scenario.options);
  assert.deepEqual(scenario.calls, ['fetch', 'disconnect', 'connect:customer1', 'subscribe']);
});

test('same SDK user reuses the connection and reads current unread state', async () => {
  const scenario = createConnectionScenario({ userID: 'customer1' });
  await initStreamChatUnreadBadge(scenario.options);
  assert.deepEqual(scenario.calls, ['fetch', 'subscribe']);
  assert.equal(scenario.storage.values.stream_chat_unread_count, '3');
});

test('reinitialization cancels old subscriptions and custom event handlers', async () => {
  const scenario = createConnectionScenario();
  await initStreamChatUnreadBadge(scenario.options);
  const oldWindow = scenario.options.windowObj;
  scenario.options = { ...scenario.options, windowObj: createWindow() };
  await initStreamChatUnreadBadge(scenario.options);
  assert.deepEqual(scenario.calls, ['fetch', 'connect:customer1', 'subscribe', 'unsubscribe', 'fetch', 'subscribe']);
  oldWindow.dispatch('stream-chat-unread-updated', { detail: { count: 99 } });
  assert.equal(scenario.storage.values.stream_chat_unread_count, '3');
  scenario.emit({ total_unread_count: 5 });
  assert.equal(scenario.storage.values.stream_chat_unread_count, '5');
});

test('switching to a visitor disconnects the live old user even when remote check is skipped', async () => {
  const scenario = createConnectionScenario();
  await initStreamChatUnreadBadge(scenario.options);
  await initStreamChatUnreadBadge({ ...scenario.options, accountContext: 'guest' });
  assert.deepEqual(scenario.calls, ['fetch', 'connect:customer1', 'subscribe', 'unsubscribe', 'disconnect']);
  scenario.emit({ total_unread_count: 99 });
  assert.equal(scenario.storage.values.stream_chat_unread_count, undefined);
  assert.equal(scenario.dot.classList.contains('hidden'), true);
});

test('404 clears unread state and the session marker', async () => {
  const scenario = createConnectionScenario({
    status: 404,
    initialStorage: {
      stream_chat_account_context: 'user:1', stream_chat_has_session: '1', stream_chat_unread_count: '4',
    },
  });
  await initStreamChatUnreadBadge(scenario.options);
  assert.equal(scenario.storage.values.stream_chat_unread_count, '0');
  assert.equal(scenario.storage.values.stream_chat_has_session, undefined);
  assert.equal(scenario.dot.classList.contains('hidden'), true);
});

test('connection and disconnection errors cannot restore another user unread state', async () => {
  for (const failure of [{ disconnectFails: true, userID: 'old-customer' }, { connectFails: true }]) {
    const scenario = createConnectionScenario({
      ...failure,
      initialStorage: { stream_chat_account_context: 'user:2', stream_chat_unread_count: '8' },
    });
    assert.equal(await initStreamChatUnreadBadge(scenario.options), null);
    assert.equal(scenario.dot.classList.contains('hidden'), true);
    assert.notEqual(scenario.storage.values.stream_chat_unread_count, '8');
    assert.equal(scenario.storage.values.stream_chat_has_session, undefined);
    assert.equal(scenario.calls.includes('subscribe'), false);
  }
});
