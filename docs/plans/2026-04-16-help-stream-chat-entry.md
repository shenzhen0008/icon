# Help Stream Chat Entry Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Move the public `/stream-chat` entry from shared navigation into the help page header card as an `在线客服` button.

**Architecture:** Keep routing and chat behavior unchanged. Remove the shared desktop navigation link, add a single help-page CTA, and update feature tests first so the behavior change is exercised through the existing Blade pages.

**Tech Stack:** Laravel 13, Blade, PHPUnit, Vite

---

### Task 1: Lock the expected behavior in feature tests

**Files:**
- Modify: `tests/Feature/Navigation/GlobalNavigationTest.php`
- Modify: `tests/Feature/Help/HelpPageTest.php`

**Step 1: Write the failing tests**

- Change navigation assertions so public and authenticated shared navigation no longer expects `Stream Chat` or `/stream-chat`.
- Extend the help page test to require `在线客服` and `/stream-chat`.

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Navigation/GlobalNavigationTest.php tests/Feature/Help/HelpPageTest.php`

Expected: FAIL because the shared navigation still renders the stream chat link and the help page does not yet include the new button.

### Task 2: Implement the minimal Blade changes

**Files:**
- Modify: `resources/views/components/nav/top.blade.php`
- Modify: `resources/views/help/index.blade.php`

**Step 1: Remove the shared nav link**

- Delete the `/stream-chat` anchor from the shared top navigation.

**Step 2: Add the help page CTA**

- Add a single `在线客服` button inside the help header card linking to `/stream-chat`.
- Reuse existing theme classes and keep the card layout stable on mobile and desktop.

**Step 3: Run the targeted tests**

Run: `php artisan test tests/Feature/Navigation/GlobalNavigationTest.php tests/Feature/Help/HelpPageTest.php`

Expected: PASS

### Task 3: Full verification

**Files:**
- Modify: `resources/views/components/nav/top.blade.php`
- Modify: `resources/views/help/index.blade.php`
- Modify: `tests/Feature/Navigation/GlobalNavigationTest.php`
- Modify: `tests/Feature/Help/HelpPageTest.php`

**Step 1: Run full test suite**

Run: `php artisan test`

Expected: PASS

**Step 2: Run frontend build**

Run: `npm run build`

Expected: PASS
