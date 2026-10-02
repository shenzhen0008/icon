const formatValue = (value, precision) => Number(value || 0).toLocaleString('en-US', {
  minimumFractionDigits: precision,
  maximumFractionDigits: precision,
});

const initializedSummaryTickerElements = new WeakSet();
const initializedExchangeMetricLists = new WeakSet();

const buildRandomInteger = (minDelta, maxDelta, precision) => {
  const multiplier = 10 ** precision;
  const min = Math.round(Number(minDelta || 0) * multiplier);
  const max = Math.round(Number(maxDelta || 0) * multiplier);
  return Math.floor(Math.random() * (max - min + 1)) + min;
};

export const buildBaseAnchoredTickValues = ({
  elementCount,
  baseValue,
  minDelta,
  maxDelta,
  precision,
  randomizer = buildRandomInteger,
}) => {
  const safeElementCount = Math.max(0, Number(elementCount || 0));
  const safePrecision = Number.isInteger(precision) ? precision : 2;
  const multiplier = 10 ** safePrecision;
  const base = Number(baseValue || 0);

  return Array.from({ length: safeElementCount }, () => {
    const nextInteger = randomizer(minDelta, maxDelta, safePrecision);
    const nextValue = base + (Number(nextInteger || 0) / multiplier);

    return formatValue(nextValue, safePrecision);
  });
};

export const startBaseAnchoredTicker = ({
  elements,
  baseValue,
  minDelta,
  maxDelta,
  stepSeconds,
  precision,
  setIntervalFn = globalThis.setInterval.bind(globalThis),
}) => {
  if (!Array.isArray(elements) || elements.length === 0) return null;

  const base = Number(baseValue || 0);
  const intervalMs = Math.max(1, Number(stepSeconds || 1)) * 1000;
  const safePrecision = Number.isInteger(precision) ? precision : 2;

  const render = () => {
    const nextValues = buildBaseAnchoredTickValues({
      elementCount: elements.length,
      baseValue: base,
      minDelta,
      maxDelta,
      precision: safePrecision,
    });

    elements.forEach((element, index) => {
      element.textContent = nextValues[index] ?? formatValue(base, safePrecision);
    });
  };

  render();
  return setIntervalFn(render, intervalMs);
};

const resolveNumberPresentation = (value) => {
  const text = String(value || '0');
  const match = text.match(/^(.*?)([-+]?[\d,]*\.?\d+)(.*?)$/);

  return {
    baseValue: parseTickerNumber(match?.[2] ?? text),
    prefix: match?.[1] ?? '',
    suffix: match?.[3] ?? '',
  };
};

export const startElementAnchoredTicker = ({
  elements,
  minDelta,
  maxDelta,
  stepSeconds,
  precision,
  randomizer,
  setIntervalFn = globalThis.setInterval.bind(globalThis),
}) => {
  if (!Array.isArray(elements) || elements.length === 0) return null;

  const presentations = elements.map((element) => resolveNumberPresentation(element.textContent));
  const intervalMs = Math.max(1, Number(stepSeconds || 1)) * 1000;
  const safePrecision = Number.isInteger(precision) ? precision : 2;

  const render = () => {
    elements.forEach((element, index) => {
      const presentation = presentations[index];
      const [value] = buildBaseAnchoredTickValues({
        elementCount: 1,
        baseValue: presentation.baseValue,
        minDelta,
        maxDelta,
        precision: safePrecision,
        randomizer,
      });
      element.textContent = `${presentation.prefix}${value}${presentation.suffix}`;
    });
  };

  render();
  return setIntervalFn(render, intervalMs);
};

const formatPresentedValue = ({ value, prefix, suffix, precision }) => `${prefix}${formatValue(value, precision)}${suffix}`;

export const startLiquidityDrivenVolumeTicker = ({
  row,
  btcVolumeMultiplier = 1,
  ethVolumeMultiplier = 1,
  minDelta,
  maxDelta,
  stepSeconds,
  precision,
  randomizer,
  setIntervalFn = globalThis.setInterval.bind(globalThis),
}) => {
  if (!row) return null;

  const pairs = [
    {
      volumeElement: row.querySelector?.('[data-field="btc_value"]'),
      liquidityElement: row.querySelector?.('[data-field="btc_liquidity"]'),
      volumeMultiplier: btcVolumeMultiplier,
    },
    {
      volumeElement: row.querySelector?.('[data-field="eth_value"]'),
      liquidityElement: row.querySelector?.('[data-field="eth_liquidity"]'),
      volumeMultiplier: ethVolumeMultiplier,
    },
  ].filter(({ volumeElement, liquidityElement }) => volumeElement && liquidityElement);

  if (pairs.length === 0) return null;

  const safePrecision = Number.isInteger(precision) ? precision : 2;
  const presentations = pairs.map(({ volumeElement, liquidityElement, volumeMultiplier }) => {
    const volumePresentation = resolveNumberPresentation(volumeElement.textContent);
    const liquidityPresentation = resolveNumberPresentation(liquidityElement.textContent);
    const multiplier = Number(volumeMultiplier || 0);

    return { volumePresentation, liquidityPresentation, multiplier };
  });
  const intervalMs = Math.max(1, Number(stepSeconds || 1)) * 1000;

  const render = () => {
    pairs.forEach(({ volumeElement, liquidityElement }, index) => {
      const presentation = presentations[index];
      const [liquidityValue] = buildBaseAnchoredTickValues({
        elementCount: 1,
        baseValue: presentation.liquidityPresentation.baseValue,
        minDelta,
        maxDelta,
        precision: safePrecision,
        randomizer,
      });
      const liquidityNumber = parseTickerNumber(liquidityValue);
      const volumeNumber = liquidityNumber * presentation.multiplier;

      liquidityElement.textContent = formatPresentedValue({
        value: liquidityNumber,
        prefix: presentation.liquidityPresentation.prefix,
        suffix: presentation.liquidityPresentation.suffix,
        precision: safePrecision,
      });
      volumeElement.textContent = formatPresentedValue({
        value: volumeNumber,
        prefix: presentation.volumePresentation.prefix,
        suffix: presentation.volumePresentation.suffix,
        precision: safePrecision,
      });
    });
  };

  render();
  return setIntervalFn(render, intervalMs);
};

const parseTickerNumber = (value) => Number(String(value || '0').replace(/[^0-9.-]/g, '')) || 0;

const nextTickerDelta = (minDelta, maxDelta, precision, randomizer = buildRandomInteger) => {
  const multiplier = 10 ** precision;
  const nextInteger = randomizer(minDelta, maxDelta, precision);

  return (Number(nextInteger || 0) / multiplier);
};

const renderSummaryTickerElement = (element, randomizer) => {
  const precision = Number(element.dataset.summaryTickerPrecision || 0);
  const minDelta = element.dataset.summaryTickerMinDelta || '0';
  const maxDelta = element.dataset.summaryTickerMaxDelta || '0';
  const suffix = element.dataset.summaryTickerSuffix || '';
  const currentValue = parseTickerNumber(element.textContent);
  const nextValue = currentValue + nextTickerDelta(minDelta, maxDelta, precision, randomizer);
  element.dataset.summaryTickerBaseValue = String(nextValue);
  element.textContent = `${formatValue(nextValue, precision)}${suffix ? ` ${suffix}` : ''}`;
};

export const startHomeSummaryTicker = ({
  root = document,
  setIntervalFn = globalThis.setInterval.bind(globalThis),
  visibilityStateProvider = () => document.visibilityState,
  randomizer,
} = {}) => {
  const participant = root.querySelector?.('#summary-participant-count');
  const totalProfit = root.querySelector?.('#summary-total-profit');
  const tickerElements = [participant, totalProfit].filter(Boolean);

  if (tickerElements.length === 0 || tickerElements.every((element) => initializedSummaryTickerElements.has(element))) {
    return null;
  }

  tickerElements.forEach((element) => {
    initializedSummaryTickerElements.add(element);
    element.dataset.homeSummaryTickerStarted = 'true';
  });

  const intervalMs = Math.max(
    1,
    Math.min(...tickerElements.map((element) => Number(element.dataset.summaryTickerStepSeconds || 3))),
  ) * 1000;

  return setIntervalFn(() => {
    if (visibilityStateProvider() === 'hidden') {
      return;
    }

    tickerElements.forEach((element) => renderSummaryTickerElement(element, randomizer));
  }, intervalMs);
};

export const startHomeExchangeMetrics = ({
  root = document,
  setIntervalFn = globalThis.setInterval.bind(globalThis),
} = {}) => {
  const list = root.querySelector?.('#exchange-metrics-list');
  if (!list || initializedExchangeMetricLists.has(list)) {
    return null;
  }

  const section = list.closest?.('section[data-shared-profit-base-value]');
  const updatedFields = Array.from(list.querySelectorAll?.('[data-field="updated_at"]') || []);
  const profitFields = Array.from(list.querySelectorAll?.('[data-field="profit_value"]') || []);
  const detailRows = Array.from(list.querySelectorAll?.('[data-detail-row]') || []);

  initializedExchangeMetricLists.add(list);
  list.dataset.homeExchangeMetricsStarted = 'true';

  const refreshUpdatedAt = () => {
    const timestamp = new Date().toLocaleString('sv-SE', { hour12: false }).replace('T', ' ');
    updatedFields.forEach((field) => {
      const prefix = field.dataset.updatedAtPrefix
        || String(field.textContent || 'Updated').split(':')[0]
        || 'Updated';
      field.dataset.updatedAtPrefix = prefix;
      field.textContent = `${prefix}: ${timestamp}`;
    });
  };

  list.querySelectorAll?.('[data-toggle-row]').forEach((button) => {
    const code = button.dataset.code;
    if (!code) return;

    const detail = list.querySelector?.(`[data-detail-row="${code}"]`);
    button.addEventListener('click', () => detail?.classList.toggle('hidden'));
  });

  refreshUpdatedAt();
  const updatedAtIntervalId = updatedFields.length > 0 ? setIntervalFn(refreshUpdatedAt, 1000) : null;
  const profitIntervalId = section && profitFields.length > 0
    ? startBaseAnchoredTicker({
      elements: profitFields,
      baseValue: section.dataset.sharedProfitBaseValue,
      minDelta: section.dataset.sharedProfitMinDelta,
      maxDelta: section.dataset.sharedProfitMaxDelta,
      stepSeconds: Number(section.dataset.sharedProfitStepSeconds || 3),
      precision: 2,
      setIntervalFn,
    })
    : null;

  const liquidityIntervalIds = detailRows
    .map((row) => startLiquidityDrivenVolumeTicker({
      row,
      btcVolumeMultiplier: section?.dataset.btcVolumeMultiplier,
      ethVolumeMultiplier: section?.dataset.ethVolumeMultiplier,
      minDelta: row.dataset.liquidityMinDelta,
      maxDelta: row.dataset.liquidityMaxDelta,
      stepSeconds: Number(row.dataset.liquidityStepSeconds || 3),
      precision: 2,
      setIntervalFn,
    }))
    .filter((intervalId) => intervalId !== null);

  return { updatedAtIntervalId, profitIntervalId, liquidityIntervalIds };
};

export const initHomeDynamicDisplay = (root = document) => {
  startHomeSummaryTicker({ root });
  startHomeExchangeMetrics({ root });
};

// Resolve the current nodes on each refresh: cached navigation replaces main.
let isRefreshingSummary = false;
export const refreshHomeSummary = async () => {
  const participant = document.getElementById('summary-participant-count');
  const totalProfit = document.getElementById('summary-total-profit');
  if (!participant || !totalProfit || document.visibilityState === 'hidden' || isRefreshingSummary) return;

  isRefreshingSummary = true;
  try {
    const response = await fetch('/home-summary', { headers: { Accept: 'application/json' } });
    if (!response.ok) return;
    const payload = await response.json();
    if (!participant.isConnected || !totalProfit.isConnected) return;
    if (typeof payload.participant_count === 'string') {
      participant.textContent = payload.participant_count;
      participant.dataset.summaryTickerBaseValue = String(parseTickerNumber(payload.participant_count));
    }
    if (typeof payload.total_profit === 'string') {
      totalProfit.textContent = `${payload.total_profit} ${totalProfit.dataset.summaryTickerSuffix || ''}`.trim();
      totalProfit.dataset.summaryTickerBaseValue = String(parseTickerNumber(payload.total_profit));
    }
  } catch (_) {
    // Statistics are retried at the next interval.
  } finally {
    isRefreshingSummary = false;
  }
};

if (typeof window !== 'undefined') {
  window.startBaseAnchoredTicker = startBaseAnchoredTicker;
  window.startHomeSummaryTicker = () => startHomeSummaryTicker();
  window.startHomeExchangeMetrics = () => startHomeExchangeMetrics();
  window.initHomeDynamicDisplay = initHomeDynamicDisplay;
  window.addEventListener('page-cache:restored', (event) => {
    if (event.detail?.pathname === '/') {
      initHomeDynamicDisplay();
    }
  });
  window.dispatchEvent(new CustomEvent('base-anchored-ticker:ready'));
  window.dispatchEvent(new CustomEvent('home-dynamic-display:ready'));

  initHomeDynamicDisplay();
  setInterval(refreshHomeSummary, 15000);
}
