# Orders Page Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add an authenticated `/me/orders` page that reuses the existing holding-product panel from the personal center.

**Architecture:** Introduce a Position module service to prepare the current user's order list data, then use that service in both the new orders page controller and the existing personal center controller. Render the orders page with the existing navigation shell and the reusable positions panel component.

**Tech Stack:** Laravel 13, Blade, Tailwind, PHPUnit, Vite

---

### Task 1: Cover the new orders page with failing tests

**Files:**
- Create: `tests/Feature/Position/PositionOrdersPageTest.php`

**Step 1: Write the failing test**

Add tests that assert:
- an authenticated user can view `/me/orders` and sees order content
- a guest is redirected when visiting `/me/orders`
- redeemed positions are hidden while open and redeeming positions are shown

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Position/PositionOrdersPageTest.php`
Expected: FAIL because the route and page do not exist yet.

### Task 2: Add the orders page backend

**Files:**
- Create: `app/Modules/Position/Http/Controllers/PositionOrdersPageController.php`
- Create: `app/Modules/Position/Services/ListUserPositionsService.php`
- Modify: `app/Modules/User/Http/Controllers/MyCenterController.php`
- Modify: `routes/web.php`

**Step 1: Write minimal implementation**

Create a service that loads the authenticated user's `open` and `redeeming` positions with their latest three daily profits. Add the new orders page controller that calls the service and returns the new view. Register `/me/orders` inside the `auth` middleware group. Update the personal center controller to reuse the same service for positions data.

**Step 2: Run test to verify it passes**

Run: `php artisan test tests/Feature/Position/PositionOrdersPageTest.php`
Expected: PASS

### Task 3: Render the orders page

**Files:**
- Create: `resources/views/orders/index.blade.php`

**Step 1: Write minimal implementation**

Build a page shell consistent with `/me`, add a page title, and render `<x-me.positions-panel :positions="$positions" />`.

**Step 2: Run focused tests**

Run: `php artisan test tests/Feature/Position/PositionOrdersPageTest.php tests/Feature/User/MyCenterPageTest.php`
Expected: PASS

### Task 4: Verify the change set

**Files:**
- Modify: `app/Modules/Position/Http/Controllers/PositionOrdersPageController.php`
- Modify: `app/Modules/Position/Services/ListUserPositionsService.php`
- Modify: `app/Modules/User/Http/Controllers/MyCenterController.php`
- Modify: `resources/views/orders/index.blade.php`
- Modify: `routes/web.php`
- Modify: `tests/Feature/Position/PositionOrdersPageTest.php`

**Step 1: Run required verification**

Run: `php artisan test`
Expected: PASS

Run: `npm run build`
Expected: PASS
