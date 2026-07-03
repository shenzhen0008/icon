import test from 'node:test';
import assert from 'node:assert/strict';

import { handleAccountCopyClick } from '../../resources/js/me/account-copy.js';

const makeCopyEvent = (button) => ({
  defaultPrevented: false,
  preventDefault() {
    this.defaultPrevented = true;
  },
  target: {
    closest(selector) {
      return selector === '[data-copy-account-button]' ? button : null;
    },
  },
});

test('handleAccountCopyClick copies account id and restores button label', async () => {
  const copied = [];
  const timers = [];
  const button = {
    dataset: {
      copyText: 'AbC123xYz987QwErT654X',
      copySuccessLabel: '已复制',
    },
    textContent: '复制账号',
  };
  const event = makeCopyEvent(button);

  const handled = await handleAccountCopyClick(event, {
    clipboard: {
      async writeText(value) {
        copied.push(value);
      },
    },
    setTimeout(callback, delay) {
      timers.push({ callback, delay });
    },
  });

  assert.equal(handled, true);
  assert.equal(event.defaultPrevented, true);
  assert.deepEqual(copied, ['AbC123xYz987QwErT654X']);
  assert.equal(button.textContent, '已复制');
  assert.equal(timers[0].delay, 1500);

  timers[0].callback();
  assert.equal(button.textContent, '复制账号');
});

test('handleAccountCopyClick shows account id when clipboard is unavailable', async () => {
  const alerts = [];
  const button = {
    dataset: {
      copyText: 'Temp987654321',
      copySuccessLabel: '已复制',
    },
    textContent: '复制账号',
  };
  const event = makeCopyEvent(button);

  const handled = await handleAccountCopyClick(event, {
    clipboard: null,
    alert(value) {
      alerts.push(value);
    },
  });

  assert.equal(handled, true);
  assert.equal(event.defaultPrevented, true);
  assert.deepEqual(alerts, ['Temp987654321']);
  assert.equal(button.textContent, '复制账号');
});
