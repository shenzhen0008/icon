import test from 'node:test';
import assert from 'node:assert/strict';

import {
  buildBaseAnchoredTickValues,
  startHomeSummaryTicker,
} from '../../../resources/js/home/dynamic-display-value.js';

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
