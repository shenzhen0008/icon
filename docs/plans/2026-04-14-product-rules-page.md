# Product Rules Page Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add a public `/products/rules` page that matches the site's current visual language while presenting rule content in a mobile-first hero-card layout.

**Architecture:** Introduce a lightweight Product module controller that returns a dedicated rules Blade page. Update the products index action button to link to the new route, and cover both the new page and the catalog entry point with feature tests.

**Tech Stack:** Laravel 13, Blade, Tailwind, PHPUnit, Vite

---

### Task 1: Cover the rules page with failing tests

**Files:**
- Create: `tests/Feature/Product/ProductRulesPageTest.php`
- Modify: `tests/Feature/Product/PublicProductCatalogTest.php`

**Step 1: Write the failing test**

Add assertions that:
- `/products/rules` returns 200 and shows the hero, value points, rules sections, and CTA
- `/products` contains `href="/products/rules"` on the "规则" entry

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Product/ProductRulesPageTest.php tests/Feature/Product/PublicProductCatalogTest.php`
Expected: FAIL because the route, page, and link do not exist yet.

### Task 2: Add the rules page backend

**Files:**
- Create: `app/Modules/Product/Http/Controllers/ProductRulesPageController.php`
- Modify: `routes/web.php`

**Step 1: Write minimal implementation**

Register `GET /products/rules` and return the dedicated rules page view.

**Step 2: Run focused tests**

Run: `php artisan test tests/Feature/Product/ProductRulesPageTest.php`
Expected: FAIL or remain incomplete until the view is added.

### Task 3: Build the rules page and connect the entry point

**Files:**
- Create: `resources/views/products/rules.blade.php`
- Modify: `resources/views/products/index.blade.php`

**Step 1: Write minimal implementation**

Create a public rules page with:
- hero section
- three quick value cards
- four explanation blocks
- bottom CTA back to `/products`

Convert the products page "规则" button into a link to `/products/rules`.

**Step 2: Run focused tests**

Run: `php artisan test tests/Feature/Product/ProductRulesPageTest.php tests/Feature/Product/PublicProductCatalogTest.php`
Expected: PASS

### Task 4: Verify the change set

**Files:**
- Modify: `app/Modules/Product/Http/Controllers/ProductRulesPageController.php`
- Modify: `resources/views/products/index.blade.php`
- Modify: `resources/views/products/rules.blade.php`
- Modify: `routes/web.php`
- Modify: `tests/Feature/Product/ProductRulesPageTest.php`
- Modify: `tests/Feature/Product/PublicProductCatalogTest.php`

**Step 1: Run required verification**

Run: `php artisan test`
Expected: PASS

Run: `npm run build`
Expected: PASS
