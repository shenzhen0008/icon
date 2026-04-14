# Products Catalog Action Buttons Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add two frontend-only action buttons, "规则" and "订单", above the summary card on the `/products` page.

**Architecture:** Keep the change limited to the existing public product catalog Blade view and cover it with a feature test that asserts the new UI labels render on `/products`. No controller, route, or behavior changes are needed because the buttons are presentational only for now.

**Tech Stack:** Laravel 13, Blade, Tailwind, PHPUnit, Vite

---

### Task 1: Cover the new catalog action buttons

**Files:**
- Modify: `tests/Feature/Product/PublicProductCatalogTest.php`

**Step 1: Write the failing test**

Add assertions in the guest catalog page test that expect `规则` and `订单` to appear in the `/products` response.

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Product/PublicProductCatalogTest.php`
Expected: FAIL because the new button labels are not yet present in the page output.

### Task 2: Render the new buttons

**Files:**
- Modify: `resources/views/products/index.blade.php`

**Step 1: Write minimal implementation**

Insert a two-column button row above the existing summary card using the current page theme styles. Use placeholder buttons with `type="button"` and no click logic.

**Step 2: Run test to verify it passes**

Run: `php artisan test tests/Feature/Product/PublicProductCatalogTest.php`
Expected: PASS with the new button labels rendered.

### Task 3: Verify delivery quality gate

**Files:**
- Modify: `resources/views/products/index.blade.php`
- Modify: `tests/Feature/Product/PublicProductCatalogTest.php`

**Step 1: Run required verification**

Run: `php artisan test`
Expected: PASS

Run: `npm run build`
Expected: PASS
