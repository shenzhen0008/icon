# Home Data Panel Record Buttons Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Hide the homepage `交易记录` and `收益记录` buttons while keeping the hero panel record pages accessible.

**Architecture:** Use a focused homepage regression test to assert the buttons are absent, then remove only the homepage button markup from the hero component. Keep route and page behavior unchanged.

**Tech Stack:** Laravel 13, Blade, PHPUnit, Vite

---

### Task 1: Lock the hidden-button behavior in tests

**Files:**
- Modify: `tests/Feature/Exchange/ExchangeMetricsPageTest.php`

**Step 1: Write the failing test**

- Add assertions that the homepage no longer renders:
  - `id="hero-trade-record-btn"`
  - `id="hero-income-record-btn"`
  - `交易记录`
  - `收入记录`

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Exchange/ExchangeMetricsPageTest.php`

Expected: FAIL because the homepage hero still renders both buttons.

### Task 2: Implement the minimal Blade change

**Files:**
- Modify: `resources/views/components/home/hero.blade.php`

**Step 1: Remove the homepage button markup**

- Delete the two-button grid from the homepage hero panel.
- Keep the mode sync script intact so the component remains stable.

**Step 2: Run the targeted test**

Run: `php artisan test tests/Feature/Exchange/ExchangeMetricsPageTest.php`

Expected: PASS

### Task 3: Full verification

**Files:**
- Modify: `resources/views/components/home/hero.blade.php`
- Modify: `tests/Feature/Exchange/ExchangeMetricsPageTest.php`

**Step 1: Run full test suite**

Run: `php artisan test`

Expected: PASS

**Step 2: Run frontend build**

Run: `npm run build`

Expected: PASS
