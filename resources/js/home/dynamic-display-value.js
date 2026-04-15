const formatValue = (value, precision) => Number(value || 0).toLocaleString('en-US', {
  minimumFractionDigits: precision,
  maximumFractionDigits: precision,
});

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
}) => {
  if (!Array.isArray(elements) || elements.length === 0) return;

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
  window.setInterval(render, intervalMs);
};

if (typeof window !== 'undefined') {
  window.startBaseAnchoredTicker = startBaseAnchoredTicker;
  window.dispatchEvent(new CustomEvent('base-anchored-ticker:ready'));
}
