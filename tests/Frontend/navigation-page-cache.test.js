import test from 'node:test';
import assert from 'node:assert/strict';

import {
  buildPageCacheKey,
  canUseSnapshotForContext,
  isPageCacheablePath,
  resolvePageCacheContext,
  shouldHandlePageCacheClick,
} from '../../resources/js/navigation-page-cache.js';

test('isPageCacheablePath allows rolled out top-level pages only', () => {
  assert.equal(isPageCacheablePath('/help'), true);
  assert.equal(isPageCacheablePath('/products'), true);
  assert.equal(isPageCacheablePath('/'), true);
  assert.equal(isPageCacheablePath('/referral'), true);
  assert.equal(isPageCacheablePath('/me'), true);
  assert.equal(isPageCacheablePath('/help/faq'), false);
  assert.equal(isPageCacheablePath('/products/1'), false);
  assert.equal(isPageCacheablePath('/me/orders'), false);
  assert.equal(isPageCacheablePath('/home-summary'), false);
  assert.equal(isPageCacheablePath('/home-hero-panel'), false);
});

test('buildPageCacheKey preserves locale query for same-origin cacheable links', () => {
  const helpKey = buildPageCacheKey('/help?locale=en', 'https://zorai.sbs');
  const productKey = buildPageCacheKey('/products?locale=en', 'https://zorai.sbs');
  const homeKey = buildPageCacheKey('/?locale=en', 'https://zorai.sbs');
  const referralKey = buildPageCacheKey('/referral?locale=en', 'https://zorai.sbs');
  const myCenterKey = buildPageCacheKey('/me?locale=en', 'https://zorai.sbs');

  assert.equal(helpKey, '/help?locale=en');
  assert.equal(productKey, '/products?locale=en');
  assert.equal(homeKey, '/?locale=en');
  assert.equal(referralKey, '/referral?locale=en');
  assert.equal(myCenterKey, '/me?locale=en');
});

test('buildPageCacheKey rejects external URLs and non-cacheable paths', () => {
  assert.equal(buildPageCacheKey('https://example.com/help', 'https://zorai.sbs'), null);
  assert.equal(buildPageCacheKey('/products/1', 'https://zorai.sbs'), null);
  assert.equal(buildPageCacheKey('/me/orders', 'https://zorai.sbs'), null);
  assert.equal(buildPageCacheKey('/home-summary', 'https://zorai.sbs'), null);
  assert.equal(buildPageCacheKey('/home-hero-panel?mode=live', 'https://zorai.sbs'), null);
});

test('shouldHandlePageCacheClick ignores modified clicks and new-tab targets', () => {
  assert.equal(shouldHandlePageCacheClick({ button: 1 }, { href: '/help' }, 'https://zorai.sbs'), false);
  assert.equal(shouldHandlePageCacheClick({ metaKey: true, button: 0 }, { href: '/help' }, 'https://zorai.sbs'), false);
  assert.equal(shouldHandlePageCacheClick({ button: 0 }, { href: '/help', target: '_blank' }, 'https://zorai.sbs'), false);
  assert.equal(shouldHandlePageCacheClick({ button: 0 }, { href: '/help' }, 'https://zorai.sbs'), true);
});

test('shouldHandlePageCacheClick handles my center only for authenticated context', () => {
  assert.equal(shouldHandlePageCacheClick({ button: 0 }, { href: '/me' }, 'https://zorai.sbs', 'guest'), false);
  assert.equal(shouldHandlePageCacheClick({ button: 0 }, { href: '/me' }, 'https://zorai.sbs', 'user:10'), true);
});

test('canUseSnapshotForContext requires matching page cache context', () => {
  assert.equal(canUseSnapshotForContext({ context: 'guest' }, 'guest'), true);
  assert.equal(canUseSnapshotForContext({ context: 'user:10' }, 'user:10'), true);
  assert.equal(canUseSnapshotForContext({ context: 'user:10' }, 'user:20'), false);
  assert.equal(canUseSnapshotForContext({ context: 'user:10' }, 'guest'), false);
  assert.equal(canUseSnapshotForContext({}, 'guest'), false);
});

test('resolvePageCacheContext falls back to global navigation context on non-cache pages', () => {
  const root = {
    querySelector(selector) {
      if (selector === '[data-page-cache-root]') {
        return null;
      }

      if (selector === '[data-page-cache-context]') {
        return { dataset: { pageCacheContext: 'user:10' } };
      }

      return null;
    },
  };

  assert.equal(resolvePageCacheContext(root), 'user:10');
});
