# Product Detail Insufficient Balance Redirect Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Redirect authenticated users to `/recharge` when a product purchase fails because their balance is insufficient.

**Architecture:** Keep purchase validation in the purchase service, but split the insufficient-balance case into a dedicated exception so the purchase controller can redirect only that branch to `/recharge`. Other validation failures remain unchanged and still redirect back with field errors.

**Tech Stack:** Laravel 13, PHP 8.3, PHPUnit feature tests

---

### Task 1: Redirect Insufficient Balance Purchases To Recharge

**Files:**
- Create: `app/Modules/Position/Exceptions/InsufficientBalanceException.php`
- Modify: `app/Modules/Position/Http/Controllers/PurchasePositionController.php`
- Modify: `app/Modules/Position/Services/PurchasePositionService.php`
- Test: `tests/Feature/Position/PurchaseProductTest.php`

**Step 1: Write the failing test**

Update the insufficient-balance purchase test to expect a redirect to `/recharge` and no validation error on `shares`.

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Position/PurchaseProductTest.php --filter=insufficient`
Expected: FAIL because the current flow redirects back to the product detail page.

**Step 3: Write minimal implementation**

Throw a dedicated insufficient-balance exception from the purchase service and catch it in the controller to return `redirect('/recharge')`.

**Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Position/PurchaseProductTest.php --filter=insufficient`
Expected: PASS

**Step 5: Run full verification**

Run:
- `php artisan test`
- `npm run build`

Expected:
- Tests pass
- Frontend build passes
