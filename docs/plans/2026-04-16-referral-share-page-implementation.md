# Referral Share Page Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Redesign `/referral` into a mobile-first share page that matches the approved reference structure, shows fixed commission rates, invite counts, invite code, and invite link in one compact page, and adapts to the site's theme system.

**Architecture:** Keep the existing route, controller, and referral service. Update the referral feature test first to define the themed share-page contract, then reshape the referral Blade view so its hero, card surfaces, nested metric panels, action buttons, and reward-help bubble all use the same theme variables and shell patterns as the rest of the public frontend.

**Tech Stack:** Laravel 13, PHP 8.3, Blade, Tailwind, PHPUnit, Vite

---

### Task 1: Write the failing referral page test

**Files:**
- Modify: `tests/Feature/Referral/ReferralDashboardPageTest.php`
- Test: `tests/Feature/Referral/ReferralDashboardPageTest.php`

**Step 1: Update the authenticated page assertions**

Change the referral page test expectations to cover:

- page title/copy for the redesigned share page
- fixed `5%` and `2%` commission values
- total first-level and second-level invite counts
- invite code section
- invite link section
- copy/share actions
- absence of the old first-level and second-level user list headings

**Step 2: Run the focused test to verify it fails**

Run:

```bash
php artisan test tests/Feature/Referral/ReferralDashboardPageTest.php
```

Expected: FAIL because the current Blade view still renders the old dashboard structure.

### Task 2: Reshape referral page data for the compact card

**Files:**
- Modify: `app/Modules/Referral/Services/GetReferralDashboardService.php`

**Step 1: Add count fields for the page**

Extend the service response with:

- `level_one_count`
- `level_two_count`

Use collection counts derived from the existing referral query results.

**Step 2: Fix commission display values**

Return `5%` and `2%` for:

- `level_1_rate`
- `level_2_rate`

This matches the approved page requirement and avoids coupling the share page presentation to admin-configured referral settings for this redesign.

**Step 3: Run the focused test**

Run:

```bash
php artisan test tests/Feature/Referral/ReferralDashboardPageTest.php
```

Expected: still FAIL, now only because the view has not been updated yet.

### Task 3: Implement the redesigned Blade page

**Files:**
- Modify: `resources/views/referral/index.blade.php`

**Step 1: Replace the dashboard layout with the share-page layout**

Build a compact page with:

- blue hero section
- single white foreground card
- commission metric blocks
- invite count blocks
- invite code block
- invite link block
- copy/share action row

**Step 2: Preserve interaction behavior**

Keep the existing JavaScript behavior for:

- copying the invite link
- invoking native share when available
- falling back to clipboard or alert

Adjust button labels to match the redesigned page copy if needed.

**Step 3: Make the page mobile-first and compact**

Ensure:

- primary content is visible within one mobile page as much as possible
- no old referral detail lists remain
- spacing stays stable on mobile and desktop

**Step 4: Run the focused test**

Run:

```bash
php artisan test tests/Feature/Referral/ReferralDashboardPageTest.php
```

Expected: PASS.

### Task 4: Adapt the page to site themes

**Files:**
- Modify: `resources/views/referral/index.blade.php`
- Test: `tests/Feature/Referral/ReferralDashboardPageTest.php`

**Step 1: Add failing assertions for themed shell usage**

Assert the referral page uses:

- `bg-theme` and `text-theme` on the page shell
- `x-layout.background-glow` output
- `bg-theme-card`, `border-theme`, and theme-variable accent classes instead of fixed hard-coded page colors

**Step 2: Replace fixed share-page colors with theme variables**

Update the Blade view so:

- page background uses the standard frontend shell
- hero uses `theme-primary` and `theme-accent`
- main card and nested metric cards use themed surfaces
- labels and support text use `text-theme` and `text-theme-secondary`
- the reward-help bubble also follows themed borders, text, and surfaces

**Step 3: Run the focused test**

Run:

```bash
php artisan test tests/Feature/Referral/ReferralDashboardPageTest.php
```

Expected: PASS.

### Task 5: Run required project verification

**Files:**
- Modify: `resources/views/referral/index.blade.php`
- Modify: `app/Modules/Referral/Services/GetReferralDashboardService.php`
- Modify: `tests/Feature/Referral/ReferralDashboardPageTest.php`

**Step 1: Run referral feature coverage**

Run:

```bash
php artisan test tests/Feature/Referral/ReferralDashboardPageTest.php
```

Expected: PASS.

**Step 2: Run full required backend verification**

Run:

```bash
php artisan test
```

Expected: PASS.

**Step 3: Run required frontend verification**

Run:

```bash
npm run build
```

Expected: PASS.
