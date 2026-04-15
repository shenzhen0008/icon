import test from 'node:test';
import assert from 'node:assert/strict';

import { loadOnchainRechargeIfNeeded, shouldLoadOnchainRecharge } from '../../resources/js/onchain-recharge-loader.js';

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

test('loadOnchainRechargeIfNeeded imports module when page needs it', async () => {
  let imported = false;
  const root = {
    querySelector: (selector) => (selector === '#home-onchain-entry' ? {} : null),
  };

  await loadOnchainRechargeIfNeeded(root, async () => {
    imported = true;
    return {};
  });

  assert.equal(imported, true);
});
