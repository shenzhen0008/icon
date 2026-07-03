const FIT_NUMBER_SELECTOR = '[data-fit-number]';
const DEFAULT_MIN_FONT_SIZE = 12;
const MAX_FIT_PASSES = 6;

const toNumber = (value) => {
  const parsed = Number.parseFloat(value);
  return Number.isFinite(parsed) ? parsed : null;
};

const resolveBaseFontSize = (element, getComputedStyle) => {
  const stored = toNumber(element.dataset.fitNumberBaseFontSize);
  if (stored !== null) {
    return stored;
  }

  const computed = toNumber(getComputedStyle(element).fontSize);
  if (computed === null) {
    return null;
  }

  element.dataset.fitNumberBaseFontSize = String(computed);
  return computed;
};

const resolveAvailableWidth = (element) => {
  const parentWidth = toNumber(element.parentElement?.clientWidth);
  if (parentWidth !== null && parentWidth > 0) {
    return parentWidth;
  }

  const ownWidth = toNumber(element.clientWidth);
  return ownWidth !== null && ownWidth > 0 ? ownWidth : null;
};

export const fitNumberText = (element, {
  getComputedStyle = globalThis.getComputedStyle,
} = {}) => {
  if (!element || typeof getComputedStyle !== 'function') {
    return false;
  }

  const baseFontSize = resolveBaseFontSize(element, getComputedStyle);
  const availableWidth = resolveAvailableWidth(element);
  if (baseFontSize === null || availableWidth === null) {
    return false;
  }

  const minFontSize = Math.max(
    1,
    toNumber(element.dataset.fitNumberMin) ?? DEFAULT_MIN_FONT_SIZE,
  );
  let currentFontSize = baseFontSize;
  element.style.fontSize = `${currentFontSize}px`;

  for (let pass = 0; pass < MAX_FIT_PASSES; pass += 1) {
    const scrollWidth = toNumber(element.scrollWidth);
    if (scrollWidth === null || scrollWidth <= availableWidth || currentFontSize <= minFontSize) {
      break;
    }

    currentFontSize = Math.max(minFontSize, currentFontSize * (availableWidth / scrollWidth));
    element.style.fontSize = `${Number(currentFontSize.toFixed(2))}px`;
  }

  return true;
};

export const fitNumberTextElements = (root = document, options = {}) => {
  root.querySelectorAll?.(FIT_NUMBER_SELECTOR).forEach((element) => {
    fitNumberText(element, options);
  });
};

export const observeFitNumberText = (root = document, {
  ResizeObserverClass = globalThis.ResizeObserver,
  ...options
} = {}) => {
  fitNumberTextElements(root, options);

  if (typeof ResizeObserverClass !== 'function') {
    return null;
  }

  const observer = new ResizeObserverClass(() => fitNumberTextElements(root, options));
  root.querySelectorAll?.(FIT_NUMBER_SELECTOR).forEach((element) => {
    observer.observe(element.parentElement || element);
  });

  return observer;
};
