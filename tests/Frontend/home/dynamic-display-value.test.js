import test from 'node:test';
import assert from 'node:assert/strict';

import { buildBaseAnchoredTickValues } from '../../../resources/js/home/dynamic-display-value.js';

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
