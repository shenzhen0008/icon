import test from 'node:test';
import assert from 'node:assert/strict';

import {
  buildBaseAnchoredTickValues,
  startElementAnchoredTicker,
  startHomeExchangeMetrics,
  startHomeSummaryTicker,
  startLiquidityDrivenVolumeTicker,
  refreshHomeSummary,
} from '../../../resources/js/home/dynamic-display-value.js';

test('summary refresh follows current DOM after cached navigation and does not apply stale responses', async () => {
  const previousDocument = globalThis.document;
  const previousFetch = globalThis.fetch;
  const makeNodes = () => ({
    'summary-participant-count': { textContent: '0', dataset: {}, isConnected: true },
    'summary-total-profit': { textContent: '0', dataset: { summaryTickerSuffix: 'USDT' }, isConnected: true },
  });
  let nodes = makeNodes();
  let calls = 0;
  let resolveFetch;
  globalThis.document = { visibilityState: 'visible', getElementById: (id) => nodes[id] };
  globalThis.fetch = () => { calls++; return new Promise((resolve) => { resolveFetch = resolve; }); };
  try {
    const pending = refreshHomeSummary();
    await refreshHomeSummary();
    assert.equal(calls, 1);
    const oldNodes = nodes;
    Object.values(oldNodes).forEach((node) => { node.isConnected = false; });
    nodes = makeNodes();
    resolveFetch({ ok: true, json: async () => ({ participant_count: '100', total_profit: '1,000.00' }) });
    await pending;
    assert.equal(oldNodes['summary-participant-count'].textContent, '0');
    assert.equal(nodes['summary-participant-count'].textContent, '0');
    const current = refreshHomeSummary();
    resolveFetch({ ok: true, json: async () => ({ participant_count: '100', total_profit: '1,000.00' }) });
    await current;
    assert.equal(nodes['summary-participant-count'].textContent, '100');
    assert.equal(nodes['summary-total-profit'].textContent, '1,000.00 USDT');
    nodes = {};
    await refreshHomeSummary();
    assert.equal(calls, 2);
  } finally {
    globalThis.document = previousDocument;
    globalThis.fetch = previousFetch;
  }
});

test('buildBaseAnchoredTickValues generates an independent value for each element', () => {
  const picks = [-2, -1, 3];
  const values = buildBaseAnchoredTickValues({
    elementCount: 3,
    baseValue: 100,
    minDelta: -2,
    maxDelta: 3,
    precision: 0,
    randomizer: () => picks.shift(),
  });

  assert.deepEqual(values, ['98', '99', '103']);
});

test('startElementAnchoredTicker preserves formatting and uses each element own base value', () => {
  const elements = [{ textContent: '$1.50' }, { textContent: '947' }];
  let intervalDelay = null;

  startElementAnchoredTicker({
    elements,
    minDelta: 2,
    maxDelta: 2,
    stepSeconds: 4,
    precision: 2,
    randomizer: () => 200,
    setIntervalFn: (callback, delay) => {
      intervalDelay = delay;
      return 1;
    },
  });

  assert.equal(elements[0].textContent, '$3.50');
  assert.equal(elements[1].textContent, '949.00');
  assert.equal(intervalDelay, 4000);
});

test('startLiquidityDrivenVolumeTicker derives volume from liquidity using configured multipliers', () => {
  const fields = {
    '[data-field="btc_value"]': { textContent: '--' },
    '[data-field="btc_liquidity"]': { textContent: '3.00' },
    '[data-field="eth_value"]': { textContent: '--' },
    '[data-field="eth_liquidity"]': { textContent: '2.00' },
  };
  const row = {
    querySelector: (selector) => fields[selector] ?? null,
  };
  let intervalDelay = null;

  startLiquidityDrivenVolumeTicker({
    row,
    btcVolumeMultiplier: 0.5,
    ethVolumeMultiplier: 5,
    minDelta: 1,
    maxDelta: 1,
    stepSeconds: 5,
    precision: 2,
    randomizer: () => 100,
    setIntervalFn: (callback, delay) => {
      intervalDelay = delay;
      return 1;
    },
  });

  assert.equal(fields['[data-field="btc_liquidity"]'].textContent, '4.00');
  assert.equal(fields['[data-field="btc_value"]'].textContent, '2.00');
  assert.equal(fields['[data-field="eth_liquidity"]'].textContent, '3.00');
  assert.equal(fields['[data-field="eth_value"]'].textContent, '15.00');
  assert.equal(intervalDelay, 5000);
});

test('startHomeSummaryTicker restarts dynamic summary values for restored home markup', () => {
  const participant = {
    dataset: {
      homeSummaryTickerStarted: 'true',
      summaryTickerBaseValue: '100',
      summaryTickerStepSeconds: '3',
      summaryTickerMinDelta: '5',
      summaryTickerMaxDelta: '5',
      summaryTickerPrecision: '0',
    },
    textContent: '100',
  };
  const totalProfit = {
    dataset: {
      homeSummaryTickerStarted: 'true',
      summaryTickerBaseValue: '2000.00',
      summaryTickerStepSeconds: '3',
      summaryTickerMinDelta: '10.00',
      summaryTickerMaxDelta: '10.00',
      summaryTickerPrecision: '2',
      summaryTickerSuffix: 'USDT',
    },
    textContent: '2,000.00 USDT',
  };
  const root = {
    querySelector(selector) {
      return {
        '#summary-participant-count': participant,
        '#summary-total-profit': totalProfit,
      }[selector] ?? null;
    },
  };

  let intervalCallback = null;
  const intervalId = startHomeSummaryTicker({
    root,
    setIntervalFn: (callback) => {
      intervalCallback = callback;
      return 10;
    },
    visibilityStateProvider: () => 'visible',
    randomizer: () => 5,
  });

  assert.equal(intervalId, 10);
  assert.equal(participant.dataset.homeSummaryTickerStarted, 'true');
  intervalCallback();

  assert.equal(participant.textContent, '105');
  assert.equal(totalProfit.textContent, '2,000.05 USDT');
});

test('startHomeExchangeMetrics owns ticker, timestamp, and row toggle initialization', () => {
  const detail = {
    dataset: {
      liquidityStepSeconds: '5',
      liquidityMinDelta: '0',
      liquidityMaxDelta: '0',
    },
    classList: {
      toggled: [],
      toggle(className) {
        this.toggled.push(className);
      },
    },
    querySelector(selector) {
      return {
        '[data-field="btc_value"]': volumeFields[0],
        '[data-field="eth_value"]': volumeFields[1],
        '[data-field="btc_liquidity"]': liquidityFields[0],
        '[data-field="eth_liquidity"]': liquidityFields[1],
      }[selector] ?? null;
    },
  };
  const button = {
    dataset: { code: 'binance' },
    addEventListener(eventName, callback) {
      assert.equal(eventName, 'click');
      this.click = callback;
    },
  };
  const updatedField = {
    dataset: {},
    textContent: 'Updated: --',
  };
  const profitField = { textContent: '100.00' };
  const volumeFields = [{ textContent: '$1.50' }, { textContent: '$2.50' }];
  const liquidityFields = [{ textContent: '947' }, { textContent: '999' }];
  const section = {
    dataset: {
      sharedProfitBaseValue: '100',
      sharedProfitMinDelta: '0',
      sharedProfitMaxDelta: '0',
      sharedProfitStepSeconds: '3',
      btcVolumeMultiplier: '0.5',
      ethVolumeMultiplier: '5',
    },
  };
  const list = {
    dataset: {},
    closest: () => section,
    querySelector: (selector) => selector === '[data-detail-row="binance"]' ? detail : null,
    querySelectorAll(selector) {
      return {
        '[data-field="updated_at"]': [updatedField],
        '[data-field="profit_value"]': [profitField],
        '[data-detail-row]': [detail],
        '[data-toggle-row]': [button],
      }[selector] ?? [];
    },
  };
  const root = {
    querySelector: (selector) => selector === '#exchange-metrics-list' ? list : null,
  };
  const intervals = [];

  const result = startHomeExchangeMetrics({
    root,
    setIntervalFn(callback, delay) {
      intervals.push({ callback, delay });
      return intervals.length;
    },
  });

  assert.deepEqual(result, {
    updatedAtIntervalId: 1,
    profitIntervalId: 2,
    liquidityIntervalIds: [3],
  });
  assert.equal(list.dataset.homeExchangeMetricsStarted, 'true');
  assert.equal(intervals[0].delay, 1000);
  assert.equal(intervals[1].delay, 3000);
  assert.equal(intervals[2].delay, 5000);
  assert.equal(profitField.textContent, '100.00');
  assert.match(updatedField.textContent, /^Updated: \d{4}-\d{2}-\d{2} /);

  button.click();
  assert.deepEqual(detail.classList.toggled, ['hidden']);

  assert.equal(startHomeExchangeMetrics({ root }), null);
});
