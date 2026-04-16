# Home Summary Font Size Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Reduce the font size of the homepage participant count and total profit summary values by one existing typography step.

**Architecture:** Keep the homepage summary markup and refresh script as-is. Update only the two targeted value nodes to use a smaller existing type scale class and cover the change with a focused feature test.

**Tech Stack:** Laravel 13, Blade, PHPUnit, Vite

---

### Task 1: Lock the typography change in a focused feature test

**Files:**
- Modify: `tests/Feature/Exchange/ExchangeMetricsPageTest.php`

**Step 1: Write the failing test**

- Add assertions that `summary-participant-count` and `summary-total-profit` render with the smaller scale class.
- Add assertions that they no longer render with `text-scale-display`.

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Exchange/ExchangeMetricsPageTest.php`

Expected: FAIL because both values still use `text-scale-display`.

### Task 2: Implement the minimal Blade change

**Files:**
- Modify: `resources/views/components/home/stats.blade.php`

**Step 1: Update the two value nodes**

- Replace `text-scale-display` with the next smaller existing scale class on:
  - `#summary-participant-count`
  - `#summary-total-profit`

**Step 2: Run the targeted test**

Run: `php artisan test tests/Feature/Exchange/ExchangeMetricsPageTest.php`

Expected: PASS

### Task 3: Full verification

**Files:**
- Modify: `resources/views/components/home/stats.blade.php`
- Modify: `tests/Feature/Exchange/ExchangeMetricsPageTest.php`

**Step 1: Run full test suite**

Run: `php artisan test`

Expected: PASS

**Step 2: Run frontend build**

Run: `npm run build`

Expected: PASS
