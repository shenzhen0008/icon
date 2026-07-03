import test from 'node:test';
import assert from 'node:assert/strict';

import {
  fitNumberText,
  fitNumberTextElements,
} from '../../resources/js/fit-number-text.js';

const createFitElement = ({
  baseFontSize = 32,
  minFontSize = '14',
  parentWidth = 180,
  textUnits = 12,
} = {}) => {
  const element = {
    dataset: {
      fitNumberMin: minFontSize,
    },
    parentElement: {
      clientWidth: parentWidth,
    },
    style: {},
    get scrollWidth() {
      const fontSize = Number.parseFloat(this.style.fontSize || `${baseFontSize}px`);
      return textUnits * fontSize;
    },
  };

  return element;
};

test('fitNumberText shrinks long numeric text to fit its parent width', () => {
  const element = createFitElement({
    baseFontSize: 32,
    parentWidth: 192,
    textUnits: 12,
  });

  const result = fitNumberText(element, {
    getComputedStyle: () => ({ fontSize: '32px' }),
  });

  assert.equal(result, true);
  assert.equal(element.style.fontSize, '16px');
  assert.equal(element.scrollWidth <= element.parentElement.clientWidth, true);
});

test('fitNumberText restores the base font size when the parent has enough width', () => {
  const element = createFitElement({
    baseFontSize: 32,
    parentWidth: 640,
    textUnits: 12,
  });
  element.dataset.fitNumberBaseFontSize = '32';
  element.style.fontSize = '16px';

  const result = fitNumberText(element, {
    getComputedStyle: () => ({ fontSize: '16px' }),
  });

  assert.equal(result, true);
  assert.equal(element.style.fontSize, '32px');
});

test('fitNumberTextElements applies fitting to all marked elements in a root', () => {
  const first = createFitElement({ baseFontSize: 30, parentWidth: 150, textUnits: 10 });
  const second = createFitElement({ baseFontSize: 30, parentWidth: 300, textUnits: 10 });
  const root = {
    querySelectorAll(selector) {
      return selector === '[data-fit-number]' ? [first, second] : [];
    },
  };

  fitNumberTextElements(root, {
    getComputedStyle: () => ({ fontSize: '30px' }),
  });

  assert.equal(first.style.fontSize, '15px');
  assert.equal(second.style.fontSize, '30px');
});
