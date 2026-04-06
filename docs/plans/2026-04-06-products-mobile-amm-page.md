# Products Mobile AMM Card Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Make `/products` match the confirmed Mobile AMM style card layout with DB-driven product card fields and a reserved stats area.

**Architecture:** Keep Laravel module boundaries: controller maps read-only product data, Blade renders UI only, migration adds display-only product metadata fields, and feature tests assert public behavior/order/rendering. No purchase business-flow changes.

**Tech Stack:** Laravel 13, Blade, Tailwind, PHP 8.3, MySQL 5.7, PHPUnit.

---

### Task 1: Add failing public catalog rendering test

**Files:**
- Modify: `tests/Feature/Product/PublicProductCatalogTest.php`

1. Add assertions for Mobile AMM card fields, reserved stats placeholder area, and icon list rendering.
2. Run target test and verify FAIL.

### Task 2: Add DB schema support for product card metadata

**Files:**
- Create: `database/migrations/2026_04_06_000000_add_catalog_fields_to_products_table.php`
- Modify: `database/sql/mvp_schema.sql`

1. Add product metadata columns needed by card rendering.
2. Keep rollback-safe migration.
3. Sync SQL snapshot.

### Task 3: Map new product fields in module layer

**Files:**
- Modify: `app/Modules/Product/Models/Product.php`
- Modify: `app/Modules/Product/Http/Controllers/PublicProductCatalogController.php`

1. Add fillable + casts for new columns.
2. Map formatted view model fields for list rendering.

### Task 4: Implement `/products` Blade redesign

**Files:**
- Modify: `resources/views/products/index.blade.php`

1. Add reserved stats placeholder block.
2. Render card layout matching confirmed sections.
3. Keep icon strip non-interactive.

### Task 5: Organize product symbol assets

**Files:**
- Move: `public/images/IMG_2843.png` ... `public/images/IMG_2849.png` -> `public/images/products/symbols/symbol-0X.png`

1. Create dedicated module-oriented image directory.
2. Keep file references stable in tests/seed data.

### Task 6: Verify

**Commands:**
- `php artisan test`
- `npm run build`

Collect outputs and report final status with changed files + known limitations.
