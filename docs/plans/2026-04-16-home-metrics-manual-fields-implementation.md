# Home Metrics Manual Fields Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Rebuild the homepage metric system so every displayed number comes from explicit admin-maintained fields, while removing the old derived summary logic, polling feed, and obsolete exchange metric columns.

**Architecture:** Add a singleton homepage display settings table for the top summary values, convert exchange metric rows to direct display fields, and update the homepage/controller/admin resources to render those values without frontend recomputation. Remove the old exchange tick/feed path and migrate existing database values into the new display-oriented structure before dropping obsolete columns.

**Tech Stack:** Laravel 13, Blade, Filament, PHP 8.3, MySQL 5.7, PHPUnit

---

### Task 1: Lock new homepage behavior with failing tests

**Files:**
- Modify: `tests/Feature/Exchange/ExchangeMetricsPageTest.php`
- Create: `tests/Feature/Admin/HomeDisplaySettingManagementPageTest.php`
- Modify: `tests/Feature/Admin/ExchangeMetricManagementPageTest.php`

**Step 1: Write the failing test**

Add assertions that:
- homepage top summary values come from dedicated backend settings, not exchange row totals
- homepage no longer exposes `/exchange-metrics` polling behavior in the rendered page
- admin can access a singleton homepage display settings page
- exchange metric admin pages now show display-field labels instead of old market-math labels

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Exchange/ExchangeMetricsPageTest.php tests/Feature/Admin/HomeDisplaySettingManagementPageTest.php tests/Feature/Admin/ExchangeMetricManagementPageTest.php`

Expected: FAIL because homepage settings resource/table does not exist yet and homepage still uses old labels / old JS.

**Step 3: Write minimal implementation**

Do not implement yet. This task ends at verified red.

**Step 4: Run test to verify it fails for the right reason**

Run the same command again if needed after fixing typos in the tests.

**Step 5: Commit**

Commit after the implementation for this task is complete.

### Task 2: Add homepage display settings and wire homepage rendering

**Files:**
- Create: `app/Modules/Home/Models/HomeDisplaySetting.php`
- Create: `database/migrations/2026_04_16_120000_create_home_display_settings_table.php`
- Create: `app/Filament/Resources/HomeDisplaySettings/HomeDisplaySettingResource.php`
- Create: `app/Filament/Resources/HomeDisplaySettings/Pages/EditHomeDisplaySetting.php`
- Create: `app/Filament/Resources/HomeDisplaySettings/Pages/ListHomeDisplaySettings.php`
- Create: `app/Filament/Resources/HomeDisplaySettings/Schemas/HomeDisplaySettingForm.php`
- Modify: `app/Modules/User/Http/Controllers/HomeController.php`
- Modify: `resources/views/components/home/stats.blade.php`

**Step 1: Write the failing test**

Use the tests from Task 1.

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Exchange/ExchangeMetricsPageTest.php tests/Feature/Admin/HomeDisplaySettingManagementPageTest.php tests/Feature/Admin/ExchangeMetricManagementPageTest.php`

Expected: FAIL before the new model/migration/resource/controller changes exist.

**Step 3: Write minimal implementation**

Implement:
- singleton table with one seeded record
- Filament singleton resource for homepage summary values
- homepage controller reading `home_summary_people_count` and `home_summary_total_profit` from that record
- summary blade rendering direct values only

**Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Exchange/ExchangeMetricsPageTest.php tests/Feature/Admin/HomeDisplaySettingManagementPageTest.php tests/Feature/Admin/ExchangeMetricManagementPageTest.php`

Expected: PASS for homepage summary ownership tests while exchange display tests may still fail until Task 3 is complete.

**Step 5: Commit**

Commit the homepage settings slice.

### Task 3: Convert exchange metrics to direct display fields and remove old runtime logic

**Files:**
- Modify: `database/migrations/2026_04_16_120000_create_home_display_settings_table.php`
- Create: `database/migrations/2026_04_16_121000_convert_exchange_metrics_to_display_fields.php`
- Modify: `app/Modules/Exchange/Models/ExchangeMetric.php`
- Modify: `app/Filament/Resources/ExchangeMetrics/Schemas/ExchangeMetricForm.php`
- Modify: `app/Filament/Resources/ExchangeMetrics/Tables/ExchangeMetricsTable.php`
- Modify: `resources/views/components/home/exchange-metrics.blade.php`
- Modify: `app/Modules/User/Http/Controllers/HomeController.php`
- Delete: `app/Modules/Exchange/Http/Controllers/ExchangeMetricsFeedController.php`
- Delete: `app/Modules/Exchange/Services/ExchangeMetricsTickService.php`
- Modify: `routes/web.php`
- Delete: `tests/Feature/Exchange/ExchangeMetricsTickServiceTest.php`
- Modify: `tests/Feature/Exchange/ExchangeMetricsPageTest.php`

**Step 1: Write the failing test**

Use the tests from Task 1 plus any schema assertions needed for new display columns.

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Exchange/ExchangeMetricsPageTest.php tests/Feature/Admin/HomeDisplaySettingManagementPageTest.php tests/Feature/Admin/ExchangeMetricManagementPageTest.php`

Expected: FAIL because old columns/labels/JS/feed still exist.

**Step 3: Write minimal implementation**

Implement:
- data migration from old numeric columns into new display string columns
- drop obsolete derived columns and logic
- homepage exchange section rendering direct display fields only
- remove `/exchange-metrics` route and tick service
- remove summary DOM rewrite / interval refresh script

**Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Exchange/ExchangeMetricsPageTest.php tests/Feature/Admin/HomeDisplaySettingManagementPageTest.php tests/Feature/Admin/ExchangeMetricManagementPageTest.php`

Expected: PASS.

**Step 5: Commit**

Commit the exchange display-field refactor.

### Task 4: Sync schema docs and run full verification

**Files:**
- Modify: `database/sql/mvp_schema.sql`
- Modify: `database/sql/icon_market.sql`
- Modify: `docs/plans/2026-04-16-home-metrics-relationship-design.md`

**Step 1: Update schema snapshots**

Reflect:
- new `home_display_settings` table
- new exchange display columns
- removed obsolete exchange metric columns

**Step 2: Run focused tests**

Run: `php artisan test tests/Feature/Exchange/ExchangeMetricsPageTest.php tests/Feature/Admin/HomeDisplaySettingManagementPageTest.php tests/Feature/Admin/ExchangeMetricManagementPageTest.php`

Expected: PASS.

**Step 3: Run full test suite**

Run: `php artisan test`

Expected: PASS.

**Step 4: Run frontend build**

Run: `npm run build`

Expected: PASS, warning about large chunks is acceptable if build exits successfully.

**Step 5: Commit**

Commit the final schema/doc sync and verification-ready state.
