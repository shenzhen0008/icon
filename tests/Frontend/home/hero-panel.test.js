import test from 'node:test';
import assert from 'node:assert/strict';

import {
  initHomeHeroPanel,
  shouldInitializeHomeHeroPanelAfterRestore,
} from '../../../resources/js/home/hero-panel.js';

const createClassList = () => {
  const values = new Set();

  return {
    toggle(className, force) {
      if (force) {
        values.add(className);
        return;
      }

      values.delete(className);
    },
    has(className) {
      return values.has(className);
    },
  };
};

const createElement = ({ textContent = '', dataset = {} } = {}) => ({
  dataset,
  textContent,
  attributes: {},
  listeners: {},
  classList: createClassList(),
  setAttribute(name, value) {
    this.attributes[name] = value;
  },
  addEventListener(type, listener) {
    this.listeners[type] = listener;
  },
  click() {
    return this.listeners.click?.();
  },
});

test('initHomeHeroPanel rebinds restored demo and live buttons', async () => {
  const panel = createElement({
    dataset: {
      modeBadgeDemo: '#demo',
      modeBadgeLive: '#live',
      locale: 'en',
      liveLoadFailed: 'Live failed',
    },
  });
  const modeBadge = createElement();
  const availableBalance = createElement();
  const totalEarnings = createElement();
  const earnings24h = createElement();
  const demoButton = createElement();
  const liveButton = createElement();
  const tradeRecordButton = createElement();
  const incomeRecordButton = createElement();
  const payloadScript = createElement({
    textContent: JSON.stringify({
      demo: {
        mode: 'demo',
        available_balance: '100.00',
        total_earnings: '10.00',
        earnings_24h: '1.00',
      },
      live: {
        mode: 'live',
        available_balance: '200.00',
        total_earnings: '20.00',
        earnings_24h: '2.00',
      },
    }),
  });
  const elements = {
    '#home-data-panel': panel,
    '#hero-mode-badge': modeBadge,
    '#hero-available-balance': availableBalance,
    '#hero-total-earnings': totalEarnings,
    '#hero-earnings-24h': earnings24h,
    '#hero-damo-btn': demoButton,
    '#hero-live-btn': liveButton,
    '#hero-trade-record-btn': tradeRecordButton,
    '#hero-income-record-btn': incomeRecordButton,
    '#home-hero-panel-payloads': payloadScript,
  };
  const root = {
    querySelector(selector) {
      return elements[selector] ?? null;
    },
  };
  const storage = {
    value: 'demo',
    getItem() {
      return this.value;
    },
    setItem(key, value) {
      this.value = value;
    },
  };
  const fetchRequests = [];
  const fetchFn = async (url) => {
    fetchRequests.push(url);

    return {
      ok: true,
      json: async () => ({
        mode: 'live',
        available_balance: '250.00',
        total_earnings: '25.00',
        earnings_24h: '2.50',
      }),
    };
  };

  initHomeHeroPanel({ root, storage, fetchFn });
  await liveButton.click();

  assert.equal(panel.dataset.homeHeroPanelStarted, 'true');
  assert.deepEqual(fetchRequests, ['/home-hero-panel?mode=live']);
  assert.equal(modeBadge.textContent, '#live');
  assert.equal(availableBalance.textContent, '$250.00');
  assert.equal(totalEarnings.textContent, '$25.00');
  assert.equal(earnings24h.textContent, '$2.50');
  assert.equal(tradeRecordButton.attributes.href, '/home/hero-panel/trade-records?mode=live&locale=en');
  assert.equal(incomeRecordButton.attributes.href, '/home/hero-panel/income-records?mode=live&locale=en');
  assert.equal(storage.value, 'live');
});

test('initHomeHeroPanel always fetches fresh live data instead of using cached seed payload', async () => {
  const panel = createElement({
    dataset: {
      modeBadgeDemo: '#demo',
      modeBadgeLive: '#live',
      locale: 'en',
      liveLoadFailed: 'Live failed',
    },
  });
  const modeBadge = createElement();
  const availableBalance = createElement();
  const totalEarnings = createElement();
  const earnings24h = createElement();
  const payloadScript = createElement({
    textContent: JSON.stringify({
      demo: {
        mode: 'demo',
        available_balance: '100.00',
        total_earnings: '10.00',
        earnings_24h: '1.00',
      },
      live: {
        mode: 'live',
        available_balance: '200.00',
        total_earnings: '20.00',
        earnings_24h: '2.00',
      },
    }),
  });
  const elements = {
    '#home-data-panel': panel,
    '#hero-mode-badge': modeBadge,
    '#hero-available-balance': availableBalance,
    '#hero-total-earnings': totalEarnings,
    '#hero-earnings-24h': earnings24h,
    '#hero-damo-btn': createElement(),
    '#hero-live-btn': createElement(),
    '#hero-trade-record-btn': createElement(),
    '#hero-income-record-btn': createElement(),
    '#home-hero-panel-payloads': payloadScript,
  };
  const root = {
    querySelector(selector) {
      return elements[selector] ?? null;
    },
  };
  const storage = {
    getItem: () => 'demo',
    setItem: () => {},
  };
  const fetchRequests = [];
  const fetchFn = async (url) => {
    fetchRequests.push(url);

    return {
      ok: true,
      json: async () => ({
        mode: 'live',
        available_balance: '321.00',
        total_earnings: '32.10',
        earnings_24h: '3.21',
      }),
    };
  };

  const api = initHomeHeroPanel({ root, storage, fetchFn });
  await api.setMode('live');

  assert.deepEqual(fetchRequests, ['/home-hero-panel?mode=live']);
  assert.equal(modeBadge.textContent, '#live');
  assert.equal(availableBalance.textContent, '$321.00');
  assert.equal(totalEarnings.textContent, '$32.10');
  assert.equal(earnings24h.textContent, '$3.21');
});

test('initHomeHeroPanel binds restored markup even when serialized started flag exists', async () => {
  const panel = createElement({
    dataset: {
      homeHeroPanelStarted: 'true',
      modeBadgeDemo: '#demo',
      modeBadgeLive: '#live',
      locale: 'en',
    },
  });
  const modeBadge = createElement();
  const liveButton = createElement();
  const payloadScript = createElement({
    textContent: JSON.stringify({
      demo: {
        mode: 'demo',
        available_balance: '100.00',
        total_earnings: '10.00',
        earnings_24h: '1.00',
      },
      live: {
        mode: 'live',
        available_balance: '200.00',
        total_earnings: '20.00',
        earnings_24h: '2.00',
      },
    }),
  });
  const elements = {
    '#home-data-panel': panel,
    '#hero-mode-badge': modeBadge,
    '#hero-available-balance': createElement(),
    '#hero-total-earnings': createElement(),
    '#hero-earnings-24h': createElement(),
    '#hero-damo-btn': createElement(),
    '#hero-live-btn': liveButton,
    '#home-hero-panel-payloads': payloadScript,
  };
  const root = {
    querySelector(selector) {
      return elements[selector] ?? null;
    },
  };

  const fetchFn = async () => ({
    ok: true,
    json: async () => ({
      mode: 'live',
      available_balance: '250.00',
      total_earnings: '25.00',
      earnings_24h: '2.50',
    }),
  });

  initHomeHeroPanel({ root, storage: { getItem: () => 'demo', setItem: () => {} }, fetchFn });
  await liveButton.click();

  assert.equal(modeBadge.textContent, '#live');
});

test('shouldInitializeHomeHeroPanelAfterRestore includes my center because it uses the same panel', () => {
  assert.equal(shouldInitializeHomeHeroPanelAfterRestore('/'), true);
  assert.equal(shouldInitializeHomeHeroPanelAfterRestore('/me'), true);
  assert.equal(shouldInitializeHomeHeroPanelAfterRestore('/help'), true);
});
