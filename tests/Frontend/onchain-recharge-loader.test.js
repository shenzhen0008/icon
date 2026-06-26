import test from 'node:test';
import assert from 'node:assert/strict';

import {
  bindDeferredOnchainRechargeLoad,
  loadOnchainRechargeIfNeeded,
  shouldLoadOnchainRecharge,
} from '../../resources/js/onchain-recharge-loader.js';

test('shouldLoadOnchainRecharge returns false when page has no onchain recharge elements', () => {
  const root = {
    querySelector: () => null,
  };

  assert.equal(shouldLoadOnchainRecharge(root), false);
});

test('shouldLoadOnchainRecharge returns true when page has onchain recharge form', () => {
  const root = {
    querySelector: (selector) => (selector === '[data-onchain-recharge-form]' ? {} : null),
  };

  assert.equal(shouldLoadOnchainRecharge(root), true);
});

test('loadOnchainRechargeIfNeeded does not import module when page does not need it', async () => {
  let imported = false;
  const root = {
    querySelector: () => null,
  };

  await loadOnchainRechargeIfNeeded(root, async () => {
    imported = true;
    return {};
  });

  assert.equal(imported, false);
});

test('loadOnchainRechargeIfNeeded imports module when dedicated recharge form exists', async () => {
  let imported = false;
  const root = {
    querySelector: (selector) => (selector === '[data-onchain-recharge-form]' ? {} : null),
  };

  await loadOnchainRechargeIfNeeded(root, async () => {
    imported = true;
    return {};
  });

  assert.equal(imported, true);
});

test('loadOnchainRechargeIfNeeded defers module when only homepage quick pay entry exists', async () => {
  let imported = false;
  const root = {
    querySelector: (selector) => (selector === '#home-onchain-entry' ? {} : null),
  };

  await loadOnchainRechargeIfNeeded(root, async () => {
    imported = true;
    return {};
  });

  assert.equal(imported, false);
});

test('bindDeferredOnchainRechargeLoad imports module after homepage quick pay click', async () => {
  let imported = 0;
  const listeners = {};
  const entry = {
    addEventListener: (event, callback) => {
      listeners[event] = callback;
    },
  };
  const root = {
    querySelector: (selector) => (selector === '#home-onchain-entry' ? entry : null),
  };

  bindDeferredOnchainRechargeLoad(root, async () => {
    imported += 1;
    return {};
  });

  assert.equal(imported, 0);
  await listeners.click();
  await listeners.click();
  assert.equal(imported, 1);
});
