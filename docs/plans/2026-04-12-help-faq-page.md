# Help FAQ Page Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build a public FAQ-style help page and expose it from the desktop navigation.

**Architecture:** Serve FAQ content from `config/help.php`, render it through a dedicated `HelpPageController`, and present it in a themed Blade page using native `details/summary` disclosure items. Navigation coverage stays consistent by adding the new route to the shared top nav and existing navigation tests.

**Tech Stack:** Laravel 13, Blade, Tailwind, PHPUnit

---

### Task 1: Add Help Page Route And View

**Files:**
- Create: `app/Modules/Help/Http/Controllers/HelpPageController.php`
- Create: `config/help.php`
- Create: `resources/views/help/index.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Help/HelpPageTest.php`

**Step 1: Write the failing test**

Add a feature test that requests `/help` and asserts the page renders expected FAQ heading and at least one configured question.

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Help/HelpPageTest.php`
Expected: FAIL because `/help` does not exist yet.

**Step 3: Write minimal implementation**

Create the controller, route, config, and themed Blade skeleton page using `details/summary`.

**Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Help/HelpPageTest.php`
Expected: PASS

### Task 2: Expose Help In Shared Navigation

**Files:**
- Modify: `resources/views/components/nav/top.blade.php`
- Modify: `tests/Feature/Navigation/GlobalNavigationTest.php`

**Step 1: Write the failing test**

Update navigation assertions to require `帮助` and `/help` on public pages and confirm password page.

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Navigation/GlobalNavigationTest.php`
Expected: FAIL because shared navigation does not yet include the help link.

**Step 3: Write minimal implementation**

Add the help link to the top navigation with active-state styling matching existing links.

**Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Navigation/GlobalNavigationTest.php`
Expected: PASS

### Task 3: Full Verification

**Files:**
- Verify only

**Step 1: Run full test suite**

Run: `php artisan test`
Expected: PASS

**Step 2: Run frontend build**

Run: `npm run build`
Expected: PASS
