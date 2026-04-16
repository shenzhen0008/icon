# Home Summary Font Size Design

**Goal:** Make the homepage `summary-participant-count` and `summary-total-profit` values slightly smaller without changing other homepage metrics.

**Context**

- Both values are rendered in `resources/views/components/home/stats.blade.php`.
- They currently use the same `text-scale-display` size as stronger hero-level numbers.
- The requested change is limited to these two values only.

**Recommended Approach**

1. Keep the existing structure and data flow unchanged.
2. Replace `text-scale-display` on the two summary value elements with the next smaller existing scale class.
3. Add a feature assertion that targets those two IDs so the behavior is locked without affecting other homepage typography.

**Why This Approach**

- Smallest possible UI change.
- Reuses the existing typography system instead of introducing new CSS.
- Keeps the rest of the homepage visual hierarchy intact.
