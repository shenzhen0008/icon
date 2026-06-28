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
  setIntervalFn = window.setInterval.bind(window),
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
  setIntervalFn = window.setInterval.bind(window),
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
  setIntervalFn = window.setInterval.bind(window),
} = {}) => {
  const list = root.querySelector?.('#exchange-metrics-list');
  if (!list || initializedExchangeMetricLists.has(list)) {
    return null;
  }

  const section = list.closest?.('section[data-shared-profit-base-value]');
  const updatedFields = Array.from(list.querySelectorAll?.('[data-field="updated_at"]') || []);
  const profitFields = Array.from(list.querySelectorAll?.('[data-field="profit_value"]') || []);

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

  return { updatedAtIntervalId, profitIntervalId };
};

export const initHomeDynamicDisplay = (root = document) => {
  startHomeSummaryTicker({ root });
  startHomeExchangeMetrics({ root });
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
}
