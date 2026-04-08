# Recharge Payment Requests Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build a recharge workflow where users must activate/login first, then submit manual remittance proof, with admin-side review list only (no auto balance credit).

**Architecture:** Add a new balance-domain request entity and a simple submit endpoint protected by auth. Keep payment receiver info configurable under `config/recharge.php`. Reuse existing guest activation modal pattern on recharge page and add a Filament resource for manual review status updates.

**Tech Stack:** Laravel 13, Blade, Tailwind, Filament, PHPUnit

---

### Task 1: Recharge request submission tests (TDD red)

**Files:**
- Create: `tests/Feature/Balance/RechargePaymentRequestTest.php`

1. Write tests for success, auth-required, and invalid-input behaviors.
2. Run targeted tests and verify failure.

### Task 2: Data model and persistence

**Files:**
- Create: `database/migrations/2026_04_08_000000_create_recharge_payment_requests_table.php`
- Create: `app/Modules/Balance/Models/RechargePaymentRequest.php`
- Modify: `database/sql/mvp_schema.sql`

1. Add table with review fields and indexes.
2. Add Eloquent model with casts/relations.
3. Sync SQL schema snapshot.

### Task 3: Recharge config + submission endpoint

**Files:**
- Create: `config/recharge.php`
- Create: `app/Modules/Balance/Http/Requests/StoreRechargePaymentRequest.php`
- Create: `app/Modules/Balance/Http/Controllers/SubmitRechargePaymentRequestController.php`
- Modify: `routes/web.php`

1. Add configurable fixed receiver info.
2. Add validated submit controller (auth required) and file upload storage.
3. Wire route under auth middleware.

### Task 4: Recharge page UX

**Files:**
- Modify: `app/Modules/Balance/Http/Controllers/RechargePageController.php`
- Modify: `resources/views/recharge/index.blade.php`

1. Render receiver info + copy action.
2. Show activation modal for guests and submission form for logged-in users.
3. Show validation and success feedback.

### Task 5: Admin review list

**Files:**
- Create: `app/Filament/Resources/RechargePaymentRequests/RechargePaymentRequestResource.php`
- Create: `app/Filament/Resources/RechargePaymentRequests/Pages/ListRechargePaymentRequests.php`
- Create: `app/Filament/Resources/RechargePaymentRequests/Tables/RechargePaymentRequestsTable.php`

1. Add list view with screenshot preview and status columns.
2. Add actions: mark processed / reject (with note).

### Task 6: Verification

**Files:**
- Modify: `tests/Feature/Admin/UserManagementPageTest.php` (only if nav assertions require update)

1. Run `php artisan test`.
2. Run `npm run build`.
3. Fix regressions and re-run.
