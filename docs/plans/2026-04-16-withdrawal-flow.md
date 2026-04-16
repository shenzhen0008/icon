# Withdrawal Flow Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build a withdrawal request workflow that debits balance immediately on submission, supports admin review with refund-on-reject, and surfaces withdrawal events in admin and home trade records.

**Architecture:** Add a dedicated `Withdrawal` module for request submission and review state, keep monetary movements in `balance_ledgers`, and update the home trade-record feed to consume a normalized mixed event stream rather than only positions. All balance mutations must happen inside database transactions with row locks on the user and request records.

**Tech Stack:** Laravel 13, Blade, Filament, MySQL 5.7, PHPUnit feature tests

---

### Task 1: Add withdrawal request persistence

**Files:**
- Create: `database/migrations/2026_04_16_150000_create_withdrawal_requests_table.php`
- Modify: `database/sql/mvp_schema.sql`

**Step 1: Write the failing test**

Add a feature test skeleton in `tests/Feature/Withdrawal/SubmitWithdrawalRequestTest.php` that expects a submitted withdrawal to exist in `withdrawal_requests`.

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=successfully_submit_withdrawal_request`
Expected: FAIL because `withdrawal_requests` table/model path does not exist yet.

**Step 3: Write minimal implementation**

- Create the migration for `withdrawal_requests`
- Add fields: `user_id`, `asset_code`, `network`, `destination_address`, `amount`, `status`, `submitted_at`, `reviewed_by`, `reviewed_at`, `review_note`, timestamps
- Add indexes for `status/submitted_at`, `user_id/submitted_at`, `asset_code/submitted_at`
- Mirror the table definition in `database/sql/mvp_schema.sql`

**Step 4: Run test to verify it passes**

Run: `php artisan test --filter=successfully_submit_withdrawal_request`
Expected: test moves past missing table failure

**Step 5: Commit**

```bash
git add database/migrations/2026_04_16_150000_create_withdrawal_requests_table.php database/sql/mvp_schema.sql tests/Feature/Withdrawal/SubmitWithdrawalRequestTest.php
git commit -m "feat: add withdrawal request persistence"
```

### Task 2: Add withdrawal module model and request validation

**Files:**
- Create: `app/Modules/Withdrawal/Models/WithdrawalRequest.php`
- Create: `app/Modules/Withdrawal/Http/Requests/StoreWithdrawalRequest.php`
- Test: `tests/Feature/Withdrawal/SubmitWithdrawalRequestTest.php`

**Step 1: Write the failing test**

Extend `SubmitWithdrawalRequestTest` with cases for:
- guest cannot submit
- destination address is required
- amount must be positive
- amount cannot exceed current balance

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Withdrawal/SubmitWithdrawalRequestTest.php`
Expected: FAIL on missing request/model/validation.

**Step 3: Write minimal implementation**

- Add `WithdrawalRequest` model with fillable/casts/relations
- Add `StoreWithdrawalRequest` validation rules
- Keep asset selection conservative, initially `USDT`

**Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Withdrawal/SubmitWithdrawalRequestTest.php`
Expected: validation tests PASS

**Step 5: Commit**

```bash
git add app/Modules/Withdrawal/Models/WithdrawalRequest.php app/Modules/Withdrawal/Http/Requests/StoreWithdrawalRequest.php tests/Feature/Withdrawal/SubmitWithdrawalRequestTest.php
git commit -m "feat: add withdrawal request validation"
```

### Task 3: Implement user submission flow

**Files:**
- Create: `app/Modules/Withdrawal/Services/SubmitWithdrawalRequestService.php`
- Create: `app/Modules/Withdrawal/Http/Controllers/SubmitWithdrawalRequestController.php`
- Modify: `routes/web.php`
- Modify: `app/Modules/Balance/Models/BalanceLedger.php`
- Test: `tests/Feature/Withdrawal/SubmitWithdrawalRequestTest.php`

**Step 1: Write the failing test**

Add a test that posts a valid withdrawal request and asserts:
- user balance decreases immediately
- `withdrawal_requests` has a `pending` row
- `balance_ledgers` has a `withdrawal_debit` row

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=deducts_balance_and_creates_pending_withdrawal_request tests/Feature/Withdrawal/SubmitWithdrawalRequestTest.php`
Expected: FAIL because route/controller/service do not exist.

**Step 3: Write minimal implementation**

- Add auth-protected POST route, e.g. `/withdrawal-requests`
- In service, open a DB transaction
- Lock user row
- Validate sufficient balance
- Decrement `users.balance`
- Create `withdrawal_requests`
- Create `balance_ledgers` with:
  - `type = withdrawal_debit`
  - `amount = -amount`
  - `biz_ref_type = withdrawal_request`
  - `biz_ref_id = request id`

**Step 4: Run test to verify it passes**

Run: `php artisan test --filter=deducts_balance_and_creates_pending_withdrawal_request tests/Feature/Withdrawal/SubmitWithdrawalRequestTest.php`
Expected: PASS

**Step 5: Commit**

```bash
git add app/Modules/Withdrawal/Services/SubmitWithdrawalRequestService.php app/Modules/Withdrawal/Http/Controllers/SubmitWithdrawalRequestController.php app/Modules/Balance/Models/BalanceLedger.php routes/web.php tests/Feature/Withdrawal/SubmitWithdrawalRequestTest.php
git commit -m "feat: implement withdrawal submission flow"
```

### Task 4: Replace send-mode placeholder UI with real form

**Files:**
- Modify: `resources/views/recharge/index.blade.php`
- Test: `tests/Feature/Withdrawal/SubmitWithdrawalRequestTest.php`
- Test: `tests/Feature/Balance/RechargePaymentRequestTest.php`

**Step 1: Write the failing test**

Add assertions that `/recharge?mode=send` shows:
- destination address input
- withdrawal amount input
- submit button
- current available balance

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=send_mode_renders_real_withdrawal_form`
Expected: FAIL because the page still shows placeholder disabled inputs.

**Step 3: Write minimal implementation**

- Replace placeholder `SEND提款` block with a real POST form
- Keep wording minimal and aligned with current design
- Preserve guest gating behavior
- Redirect successful submission back to `/recharge?mode=send`

**Step 4: Run test to verify it passes**

Run: `php artisan test --filter=send_mode_renders_real_withdrawal_form`
Expected: PASS

**Step 5: Commit**

```bash
git add resources/views/recharge/index.blade.php tests/Feature/Withdrawal/SubmitWithdrawalRequestTest.php tests/Feature/Balance/RechargePaymentRequestTest.php
git commit -m "feat: connect recharge send mode to withdrawal form"
```

### Task 5: Add admin withdrawal review resource

**Files:**
- Create: `app/Filament/Resources/WithdrawalRequests/WithdrawalRequestResource.php`
- Create: `app/Filament/Resources/WithdrawalRequests/Pages/ListWithdrawalRequests.php`
- Create: `app/Filament/Resources/WithdrawalRequests/Tables/WithdrawalRequestsTable.php`
- Test: `tests/Feature/Admin/WithdrawalRequestManagementPageTest.php`

**Step 1: Write the failing test**

Add an admin feature test that visits the withdrawal request list and asserts the pending request is visible.

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Admin/WithdrawalRequestManagementPageTest.php`
Expected: FAIL because the Filament resource does not exist.

**Step 3: Write minimal implementation**

- Add a Filament resource with list page only
- Show user, asset, network, destination address, amount, status, submitted time, reviewer, reviewed time

**Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Admin/WithdrawalRequestManagementPageTest.php`
Expected: PASS

**Step 5: Commit**

```bash
git add app/Filament/Resources/WithdrawalRequests tests/Feature/Admin/WithdrawalRequestManagementPageTest.php
git commit -m "feat: add admin withdrawal request list"
```

### Task 6: Implement admin mark-processed action

**Files:**
- Create: `app/Modules/Withdrawal/Services/ReviewWithdrawalRequestService.php`
- Modify: `app/Filament/Resources/WithdrawalRequests/Tables/WithdrawalRequestsTable.php`
- Test: `tests/Feature/Withdrawal/ReviewWithdrawalRequestServiceTest.php`

**Step 1: Write the failing test**

Add a test for marking a pending request as processed and assert:
- request becomes `processed`
- reviewer fields are saved
- user balance does not change again

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=mark_processed_updates_withdrawal_request_without_second_debit`
Expected: FAIL because the service/action does not exist.

**Step 3: Write minimal implementation**

- Add `markProcessed()` to review service
- Lock the request
- Ensure status is `pending`
- Update only review fields and status
- Wire Filament action to service

**Step 4: Run test to verify it passes**

Run: `php artisan test --filter=mark_processed_updates_withdrawal_request_without_second_debit`
Expected: PASS

**Step 5: Commit**

```bash
git add app/Modules/Withdrawal/Services/ReviewWithdrawalRequestService.php app/Filament/Resources/WithdrawalRequests/Tables/WithdrawalRequestsTable.php tests/Feature/Withdrawal/ReviewWithdrawalRequestServiceTest.php
git commit -m "feat: add withdrawal processed review action"
```

### Task 7: Implement reject-and-refund flow

**Files:**
- Modify: `app/Modules/Withdrawal/Services/ReviewWithdrawalRequestService.php`
- Modify: `app/Filament/Resources/WithdrawalRequests/Tables/WithdrawalRequestsTable.php`
- Test: `tests/Feature/Withdrawal/ReviewWithdrawalRequestServiceTest.php`

**Step 1: Write the failing test**

Add a test that rejects a pending withdrawal and asserts:
- request becomes `rejected`
- balance is restored
- `balance_ledgers` has a `withdrawal_refund` row

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=rejecting_withdrawal_refunds_balance_and_creates_refund_ledger`
Expected: FAIL because refund logic does not exist.

**Step 3: Write minimal implementation**

- Add `reject()` path in review service
- Lock request and user
- Ensure request is still `pending`
- Credit balance back
- Create `withdrawal_refund` ledger entry
- Save reviewer metadata and review note

**Step 4: Run test to verify it passes**

Run: `php artisan test --filter=rejecting_withdrawal_refunds_balance_and_creates_refund_ledger`
Expected: PASS

**Step 5: Commit**

```bash
git add app/Modules/Withdrawal/Services/ReviewWithdrawalRequestService.php app/Filament/Resources/WithdrawalRequests/Tables/WithdrawalRequestsTable.php tests/Feature/Withdrawal/ReviewWithdrawalRequestServiceTest.php
git commit -m "feat: add withdrawal rejection refund flow"
```

### Task 8: Normalize home trade record data

**Files:**
- Modify: `app/Modules/Home/Services/HomeHeroPanelService.php`
- Modify: `app/Modules/Home/Http/Controllers/HeroPanelTradeRecordsPageController.php`
- Modify: `resources/views/home/hero-panel-trade-records.blade.php`
- Test: `tests/Feature/Home/HomeHeroPanelFeedTest.php`
- Test: `tests/Feature/Home/HeroPanelRecordPagesTest.php`

**Step 1: Write the failing test**

Add tests asserting live trade records can include:
- purchase event
- withdrawal debit event
- withdrawal refund event

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Home/HomeHeroPanelFeedTest.php tests/Feature/Home/HeroPanelRecordPagesTest.php`
Expected: FAIL because home trade records still read only `positions`.

**Step 3: Write minimal implementation**

- Query `balance_ledgers` for supported event types
- Map them to a normalized display structure:
  - `event_type`
  - `title`
  - `amount`
  - `status`
  - `occurred_at`
- Update trade-record page headings and table columns to match generic event data

**Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Home/HomeHeroPanelFeedTest.php tests/Feature/Home/HeroPanelRecordPagesTest.php`
Expected: PASS

**Step 5: Commit**

```bash
git add app/Modules/Home/Services/HomeHeroPanelService.php app/Modules/Home/Http/Controllers/HeroPanelTradeRecordsPageController.php resources/views/home/hero-panel-trade-records.blade.php tests/Feature/Home/HomeHeroPanelFeedTest.php tests/Feature/Home/HeroPanelRecordPagesTest.php
git commit -m "feat: show withdrawal events in home trade records"
```

### Task 9: Run focused regression checks

**Files:**
- Test: `tests/Feature/Withdrawal/*`
- Test: `tests/Feature/Admin/WithdrawalRequestManagementPageTest.php`
- Test: `tests/Feature/Balance/RechargePaymentRequestTest.php`
- Test: `tests/Feature/Home/*`

**Step 1: Run focused PHPUnit suite**

Run:

```bash
php artisan test tests/Feature/Withdrawal tests/Feature/Admin/WithdrawalRequestManagementPageTest.php tests/Feature/Balance/RechargePaymentRequestTest.php tests/Feature/Home
```

Expected: PASS

**Step 2: Run frontend build**

Run:

```bash
npm run build
```

Expected: PASS

**Step 3: Commit**

```bash
git add .
git commit -m "test: verify withdrawal flow integration"
```
