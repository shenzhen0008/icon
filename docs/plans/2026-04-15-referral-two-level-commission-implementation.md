# 两级推荐提成 MVP 实施方案

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** 实现 MVP 版本的两级推荐提成：用户通过邀请码注册绑定上级，管理员在后台设置一级/二级比例，系统按上线后的正向结算收益批量发放提成。

**Architecture:** 新增 `app/Modules/Referral` 承载邀请码绑定、提成配置读取与批处理发放。推荐关系先落在 `users.invite_code` 与 `users.referrer_id`，邀请码捕获使用 session + signed cookie + hidden input 以兼容钱包内置浏览器，提成比例通过单条后台配置维护，发放结果写入 `referral_commission_records` 并复用现有 `balance_ledgers` 入账。MVP 保留资金幂等，不做配置版本、推荐关系审计表、提成记录后台列表和复杂状态机。

**Tech Stack:** Laravel 13、PHP 8.3、MySQL 5.7、Blade、Filament、PHPUnit、Vite

---

### Task 1: Write MVP Failing Tests

**Files:**
- Create: `tests/Feature/Referral/BindReferrerOnRegisterTest.php`
- Create: `tests/Feature/Referral/ReferralCommissionSettingManagementPageTest.php`
- Create: `tests/Feature/Referral/ProcessReferralCommissionCommandTest.php`

**Step 1: Write registration binding tests**

Cover:
- Visiting a URL with `?invite_code=xxx` stores the invite code in session and signed cookie.
- Register pages and modal forms render the current invite code into a hidden `invite_code` input.
- Registration binding priority is `POST invite_code`, then session, then signed cookie.
- Registering with a valid invite code sets the new user's `referrer_id`.
- Registering with an invalid invite code does not set `referrer_id`.
- Self-invite does not set `referrer_id`.
- An already authenticated user visiting an invite link does not overwrite their existing referral relation.
- New registered users receive their own unique `invite_code`.

**Step 2: Write admin ratio setting tests**

Cover:
- Admin can open the referral commission setting page.
- Admin can save `level_1_rate` and `level_2_rate`.
- Invalid ratio fails validation when `level_2_rate > level_1_rate`.
- Invalid ratio fails validation when either rate is negative or `level_1_rate >= 1`.
- Disabled setting prevents commission processing.

**Step 3: Write commission command tests**

Cover:
- `A <- B <- C`: when C has a positive settlement, B receives level 1 commission and A receives level 2 commission.
- `profit <= 0` does not create commission records.
- `settlement_date < config('referral.go_live_date')` does not create commission records.
- Re-running `referral:commission-process` does not duplicate commission records, balance, or ledgers.
- Missing or inactive setting skips processing.

**Step 4: Run the focused tests and confirm the baseline fails**

Run:

```bash
php artisan test tests/Feature/Referral/BindReferrerOnRegisterTest.php tests/Feature/Referral/ReferralCommissionSettingManagementPageTest.php tests/Feature/Referral/ProcessReferralCommissionCommandTest.php
```

Expected: FAIL because the Referral module, migrations, setting resource, and command do not exist yet.

---

### Task 2: Add MVP Data Structures

**Files:**
- Create: `database/migrations/2026_04_15_030000_add_referral_columns_to_users_table.php`
- Create: `database/migrations/2026_04_15_040000_create_referral_commission_records_table.php`
- Create: `database/migrations/2026_04_15_050000_create_referral_commission_settings_table.php`
- Modify: `database/sql/mvp_schema.sql`
- Modify: `tests/Feature/Reservation/ReservationSchemaTest.php`
- Modify: `app/Models/User.php`

**Step 1: Add referral columns to users**

Add:
- `invite_code` nullable unique string.
- `referrer_id` nullable foreign key to `users.id`, `nullOnDelete`.

Also update `User` fillable/casts as needed.

**Step 2: Create `referral_commission_records`**

Fields:
- `id`
- `settlement_id`
- `level`
- `referrer_id`
- `referred_user_id`
- `base_profit`
- `commission_rate`
- `commission_amount`
- `status`
- `granted_at`
- `failed_reason`
- timestamps

Constraints and indexes:
- Unique `(settlement_id, level)`.
- Foreign keys to `daily_settlements` and `users`.
- Index `(referrer_id, granted_at)`.
- Index `(referred_user_id, granted_at)`.
- Index `(status, id)`.

**Step 3: Create `referral_commission_settings`**

Fields:
- `id`
- `level_1_rate`
- `level_2_rate`
- `is_active`
- timestamps

MVP rule:
- Use fixed row `id = 1`.
- Enforce ratio validation in application code because MySQL 5.7 check constraints are not reliable enough for this baseline.

**Step 4: Sync schema snapshot**

Update `database/sql/mvp_schema.sql` in the same change set.

**Step 5: Add schema assertions**

Extend the existing schema test to assert:
- `users.invite_code`
- `users.referrer_id`
- `referral_commission_records`
- `referral_commission_settings`
- required indexes and unique keys

**Step 6: Run schema tests**

Run:

```bash
php artisan test tests/Feature/Reservation/ReservationSchemaTest.php
```

Expected: PASS.

---

### Task 3: Implement Invite Capture and Registration Binding

**Files:**
- Create: `app/Modules/Referral/Http/Middleware/CaptureInviteCodeMiddleware.php`
- Create: `app/Modules/Referral/Services/BindReferrerOnRegisterService.php`
- Create: `app/Modules/Referral/Support/InviteCodeGenerator.php`
- Create: `config/referral.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`
- Modify: `app/Modules/User/Http/Controllers/Auth/RegisteredUserController.php`
- Modify: `app/Modules/User/Services/AccountActivationService.php`
- Modify: `resources/views/auth/register.blade.php`
- Modify: `resources/views/welcome.blade.php`
- Modify: `resources/views/products/show.blade.php`
- Modify: `resources/views/me/index.blade.php`
- Modify: `resources/views/recharge/index.blade.php`

**Step 1: Add MVP referral config**

Create `config/referral.php` with:
- `enabled`
- `go_live_date`
- `business_timezone`
- `invite_code_session_key`
- `invite_code_cookie_name`
- `invite_code_cookie_minutes`
- `invite_code_length`
- `batch_chunk_size`

Keep commission rates out of config because admins manage them in the database setting.

**Step 2: Implement invite code generator**

Generate short alphanumeric invite codes and retry on unique collisions.

**Step 3: Implement capture middleware**

Behavior:
- If request has `invite_code`, validate simple format such as `^[A-Za-z0-9]{6,32}$`.
- If the user is a guest, store it in session using `config('referral.invite_code_session_key')`.
- If the user is a guest, also queue a signed cookie using `config('referral.invite_code_cookie_name')` and `config('referral.invite_code_cookie_minutes')`.
- If the user is authenticated, do not overwrite their existing referral state.
- Do not query or write the database in middleware.

**Step 4: Add invite code to registration forms**

Add an `invite_code` hidden input to the full register page and modal register forms.

MVP behavior:
- Use old input first.
- Otherwise use the session invite code.
- Otherwise use the signed cookie invite code.

**Step 5: Bind referrer after account creation**

`BindReferrerOnRegisterService` should:
- Read submitted `invite_code`, falling back to session, then signed cookie.
- Find `users.invite_code`.
- Reject invalid code and self-invite.
- Only set `referrer_id` if the new user has none.
- Ensure the new user has a unique `invite_code`.
- Clear the session invite code and signed cookie after registration completes.

**Step 6: Wire into registration flow**

In `RegisteredUserController`, bind the referrer after `AccountActivationService` creates the user and before redirecting.

Be careful with `session()->regenerate()` so the invite code is read before it is lost. If session is lost in a wallet in-app browser, the signed cookie and hidden input should still preserve the invite code for the registration request.

**Step 7: Run registration tests**

Run:

```bash
php artisan test tests/Feature/Referral/BindReferrerOnRegisterTest.php tests/Feature/Auth/AuthenticationFlowTest.php tests/Feature/User/MyCenterPageTest.php
```

Expected: PASS.

---

### Task 4: Implement Admin Ratio Setting

**Files:**
- Create: `app/Modules/Referral/Models/ReferralCommissionSetting.php`
- Create: `app/Modules/Referral/Services/GetReferralCommissionSettingService.php`
- Create: `app/Filament/Resources/ReferralCommissionSettings/ReferralCommissionSettingResource.php`
- Create: `app/Filament/Resources/ReferralCommissionSettings/Schemas/ReferralCommissionSettingForm.php`
- Create: `app/Filament/Resources/ReferralCommissionSettings/Pages/EditReferralCommissionSetting.php`
- Modify: `database/seeders/DatabaseSeeder.php` if the project uses seed defaults for admin settings

**Step 1: Add setting model**

Create an Eloquent model for `referral_commission_settings`.

Casts:
- `level_1_rate` as decimal with 4 places
- `level_2_rate` as decimal with 4 places
- `is_active` as boolean

**Step 2: Add setting retrieval service**

`GetReferralCommissionSettingService` should:
- Return row `id = 1` when `is_active = true`.
- Return `null` when missing or inactive.

**Step 3: Add Filament setting resource**

MVP behavior:
- Expose a single edit page for row `id = 1`.
- Allow editing `level_1_rate`, `level_2_rate`, `is_active`.
- Validate:
  - `level_1_rate` required, numeric, min `0`, less than `1`
  - `level_2_rate` required, numeric, min `0`, less than or equal to `level_1_rate`

**Step 4: Ensure default row exists**

Choose one:
- Seed `id = 1` with `level_1_rate = 0.05`, `level_2_rate = 0.02`, `is_active = true`.
- Or create it lazily when the Filament edit page opens.

Prefer seeding if the project already seeds operational defaults.

**Step 5: Run admin setting tests**

Run:

```bash
php artisan test tests/Feature/Referral/ReferralCommissionSettingManagementPageTest.php
```

Expected: PASS.

---

### Task 5: Implement Commission Processing Command

**Files:**
- Create: `app/Modules/Referral/Models/ReferralCommissionRecord.php`
- Create: `app/Modules/Referral/Services/GrantReferralCommissionForSettlementService.php`
- Create: `app/Modules/Referral/Services/ProcessReferralCommissionBatchService.php`
- Create: `app/Modules/Referral/Console/Commands/ProcessReferralCommissionCommand.php`
- Modify: `app/Modules/Balance/Models/BalanceLedger.php`
- Modify: `routes/console.php`
- Modify: `bootstrap/app.php` if command registration requires it in this Laravel setup

**Step 1: Add commission record model**

Create an Eloquent model with fillable fields and casts for:
- money fields as decimal
- `commission_rate` as decimal with 4 places
- `granted_at` as datetime

**Step 2: Implement single settlement grant service**

For one `DailySettlement`:
- Return early when referral is disabled in config.
- Return early when active setting is missing.
- Return early when `profit <= 0`.
- Return early when `settlement_date < config('referral.go_live_date')`.
- Resolve level 1 referrer from `settlement.user.referrer_id`.
- Resolve level 2 referrer from level 1 referrer's `referrer_id`.
- Skip missing levels.
- Calculate commission amount with two decimal places.
- Use the active setting rate as a snapshot in `referral_commission_records`.

**Step 3: Implement transaction per level**

For each level, inside one database transaction:
- Attempt to create or find the commission record for `(settlement_id, level)`.
- If the existing record is `success`, return without changing balance.
- Lock the referrer user row.
- Use ledger business key `settlement:{settlement_id}:level:{level}`.
- If a matching ledger already exists, mark the commission record `success` and return.
- Create `balance_ledgers` row:
  - `type = referral_commission_credit`
  - `biz_ref_type = referral_commission`
  - `biz_ref_id = settlement:{settlement_id}:level:{level}`
- Increase the referrer's `users.balance`.
- Mark commission record `success` and set `granted_at`.
- On exception, mark commission record `failed` with `failed_reason` where possible, then rethrow or continue according to batch service behavior.

**Step 4: Implement batch service**

Scan `daily_settlements` by ID in chunks:
- `profit > 0`
- `settlement_date >= go_live_date`
- ordered by `id`

For each settlement, call the single settlement grant service.

Return simple stats:
- scanned
- granted
- skipped
- failed

**Step 5: Implement Artisan command**

Command name:

```bash
referral:commission-process
```

Behavior:
- Calls `ProcessReferralCommissionBatchService`.
- Prints stats.
- Exits successfully when disabled or setting missing, but prints why processing was skipped.

**Step 6: Run command tests**

Run:

```bash
php artisan test tests/Feature/Referral/ProcessReferralCommissionCommandTest.php
```

Expected: PASS.

---

### Task 6: Final Verification and Delivery

**Files:**
- Modify: `README.md` only if new operational commands or admin setup need documentation.
- Modify: `docs/plans/推荐返利-邀请裂变.md` only if final MVP behavior changes during implementation.

**Step 1: Run all backend tests**

Run:

```bash
php artisan test
```

Expected: PASS.

**Step 2: Run frontend build**

Run:

```bash
npm run build
```

Expected: PASS.

**Step 3: Manual smoke test**

- Open an invite link such as `/?invite_code=ABC123`.
- Register a new account.
- Confirm the new user has `referrer_id`.
- Edit referral rates in the admin panel.
- Prepare a positive `daily_settlements` record for the referred user.
- Run `php artisan referral:commission-process`.
- Confirm:
  - `referral_commission_records` contains expected level records.
  - Referrer balances increased.
  - `balance_ledgers` contains `referral_commission_credit` rows.
  - Running the command again does not change balances again.

**Step 4: Delivery notes**

Include:
- Changed files
- Executed commands
- Verification result
- Known limitations:
  - No recommendation relationship audit table.
  - No configuration history.
  - No localStorage invite fallback.
  - No commission record admin list in MVP.
