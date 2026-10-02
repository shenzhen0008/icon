import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const blade = readFileSync(new URL('../../../resources/views/components/popup-push/pending.blade.php', import.meta.url), 'utf8');
const script = blade.match(/<script>([\s\S]*?)<\/script>/)[1]
  .replace(/@json\(csrf_token\(\)\)/g, '"test-csrf"')
  .replace(/@json\(__\('pages\/home\.stats\.popup_salutation_default'\)\)/g, '"User"');
const tick = () => new Promise((resolve) => setImmediate(resolve));
const response = (body, ok = true) => ({ ok, json: async () => body });
const popup = { campaign_id: 1, username: 'Alice', content: '<img src=x onerror=alert(1)>\nNotice', requires_ack: false };

function mount({ context = 'user:1', loading = false, values = new Map(), storageFails = false, read = async () => response({ popup }) } = {}) {
  const document = new EventTarget();
  document.readyState = loading ? 'loading' : 'complete';
  const window = new EventTarget();
  const nodes = Object.fromEntries(['modal', 'salutation', 'body', 'error', 'confirm'].map((name) => [name, Object.assign(new EventTarget(), {
    textContent: '', open: false, hidden: true, disabled: false,
  })]));
  nodes.modal.showModal = () => { nodes.modal.open = true; };
  nodes.modal.close = () => { nodes.modal.open = false; };
  document.getElementById = (id) => id === 'top-nav'
    ? { dataset: { pageCacheContext: context } }
    : nodes[id.replace('pending-popup-', '')];
  const requests = [];
  let receipt = async () => response({ ok: true });
  const storage = {
    getItem(key) { if (storageFails) throw Error('blocked'); return values.get(key) ?? null; },
    setItem(key, value) { if (storageFails) throw Error('blocked'); values.set(key, value); },
  };
  runInNewContext(script, { document, window, localStorage: storage, fetch: async (url, options) => {
    requests.push({ url, options });
    return url === '/popup/pending' ? read() : receipt(url);
  } });
  return { document, window, nodes, requests, values, setReceipt(fn) { receipt = fn; }, restore() { window.dispatchEvent(new Event('page-cache:restored')); } };
}

test('executes the actual component script: text rendering, CSRF, receipts, deduplication', async () => {
  const app = mount();
  await tick();
  assert.equal(app.nodes.modal.open, true);
  assert.match(app.nodes.body.textContent, /<img src=x onerror=alert\(1\)>/);
  assert.equal(app.requests[0].options.headers.Accept, 'application/json');
  assert.equal(app.requests[1].options.headers['X-CSRF-TOKEN'], 'test-csrf');
  app.restore();
  assert.equal(app.requests.length, 2);
  const cancel = new Event('cancel', { cancelable: true });
  app.nodes.modal.dispatchEvent(cancel);
  assert.equal(cancel.defaultPrevented, true);
  app.nodes.confirm.dispatchEvent(new Event('click'));
  await tick();
  assert.equal(app.nodes.modal.open, false);
  assert.equal(app.requests.length, 3); // No extra query after confirmation.
  app.restore();
  await tick();
  assert.equal(app.nodes.modal.open, false);
  assert.equal(app.requests.length, 4);
});

test('guest never requests; DOM ready initializes once; in-flight restoration is skipped', async () => {
  const guest = mount({ context: 'guest' });
  guest.restore();
  assert.equal(guest.requests.length, 0);
  let resolveRead;
  const app = mount({ loading: true, read: () => new Promise((resolve) => { resolveRead = resolve; }) });
  assert.equal(app.requests.length, 0);
  app.document.dispatchEvent(new Event('DOMContentLoaded'));
  app.document.dispatchEvent(new Event('DOMContentLoaded'));
  app.restore();
  app.restore();
  assert.equal(app.requests.length, 1);
  resolveRead(response({ popup }));
  await tick();
  assert.equal(app.requests.length, 2); // Pending + shown, no queued checks.
});

test('confirmation failures preserve the dialog and allow retry; duplicate submissions are blocked', async () => {
  for (const fail of [async () => response({}, false), async () => { throw Error('network'); }, async () => response({ ok: false }), async () => ({ ok: true, json: async () => { throw Error('invalid JSON'); } })]) {
    const app = mount();
    await tick();
    app.setReceipt(fail);
    app.nodes.confirm.dispatchEvent(new Event('click'));
    app.nodes.confirm.dispatchEvent(new Event('click'));
    await tick();
    assert.equal(app.nodes.modal.open, true);
    assert.equal(app.nodes.error.hidden, false);
    assert.equal(app.nodes.confirm.disabled, false);
    assert.equal(app.requests.filter(({ url }) => url.endsWith('/confirm')).length, 1);
    app.setReceipt(async () => response({ ok: true }));
    app.nodes.confirm.dispatchEvent(new Event('click'));
    await tick();
    assert.equal(app.nodes.modal.open, false);
  }
});

test('persistent records survive navigation, isolate users and synchronize storage events', async () => {
  const values = new Map([['popup_push_shown_campaign_ids:user:1', '[1]']]);
  const first = mount({ values });
  const other = mount({ context: 'user:2', values });
  await tick();
  assert.equal(first.nodes.modal.open, false);
  assert.equal(other.nodes.modal.open, true);
  let pending = null;
  const synchronized = mount({ read: async () => response({ popup: pending }), values: new Map() });
  await tick();
  synchronized.values.set('popup_push_shown_campaign_ids:user:1', '[1]');
  const event = new Event('storage');
  event.key = 'popup_push_shown_campaign_ids:user:1';
  synchronized.window.dispatchEvent(event);
  pending = popup;
  synchronized.restore();
  await tick();
  assert.equal(synchronized.nodes.modal.open, false);
});

test('storage failures keep memory deduplication, old keys are ignored, invalid reads are retriable', async () => {
  const app = mount({ storageFails: true });
  await tick();
  app.nodes.confirm.dispatchEvent(new Event('click'));
  await tick();
  app.restore();
  await tick();
  assert.equal(app.nodes.modal.open, false);
  const old = mount({ values: new Map([['home_popup_shown_campaign_ids', '[1]']]) });
  await tick();
  assert.equal(old.nodes.modal.open, true);
  for (const invalid of [async () => response({}, false), async () => { throw Error('offline'); }, async () => response({ popup: { ...popup, campaign_id: '1' } })]) {
    const failed = mount({ read: invalid });
    await tick();
    failed.restore();
    await tick();
    assert.equal(failed.requests.length, 2);
    assert.equal(failed.nodes.modal.open, false);
    assert.equal(failed.values.size, 0);
  }
});

test('failed dialog opening does not record shown or send a receipt', async () => {
  const app = mount();
  app.nodes.modal.showModal = () => { throw Error('cannot open'); };
  await tick();
  assert.equal(app.values.size, 0);
  assert.equal(app.requests.length, 1);
});
