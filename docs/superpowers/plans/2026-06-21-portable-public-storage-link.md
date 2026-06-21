# Portable Public Storage Link Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make Laravel's `public/storage` link portable across whole-directory archives and Linux Git checkouts without changing upload paths or URLs.

**Architecture:** Keep Laravel's existing `storage/app/public` public-disk root and replace only the environment-specific absolute symbolic link with a tracked relative symbolic link. No application, database, or filesystem-disk configuration changes are required.

**Tech Stack:** Laravel 13, Git symbolic links, Unix filesystem

---

### Task 1: Track a portable storage link

**Files:**
- Modify: `.gitignore`
- Replace link: `public/storage`
- Create: `docs/superpowers/plans/2026-06-21-portable-public-storage-link.md`

- [x] **Step 1: Confirm the current failure condition**

Run: `readlink public/storage`

Expected before implementation: an absolute machine-specific path such as `/Users/linke/hui/icon-market/storage/app/public`.

- [x] **Step 2: Allow Git to track the link**

Remove the following line from `.gitignore`:

```gitignore
/public/storage
```

- [x] **Step 3: Replace the absolute link with a relative link**

Run using the operating system's symbolic-link support (the project does not install Laravel's optional `symfony/filesystem` dependency required by `storage:link --relative`):

```bash
test ! -e public/storage
ln -s ../storage/app/public public/storage
```

Expected: `public/storage` resolves to the unchanged `storage/app/public` directory.

- [x] **Step 4: Verify the link and Git representation**

Run:

```bash
readlink public/storage
git add .gitignore public/storage
git ls-files -s public/storage
test -f public/storage/recharge-receipts/0hm5VvvCODE1ODVm1ROLfAz3Jrd1XRSdSqlbZ3tv.jpg
```

Expected: link target is `../storage/app/public`, Git mode is `120000`, and the existing receipt remains reachable through the link.

### Task 2: Run project quality gates

**Files:**
- Verify only: all application files

- [x] **Step 1: Run backend tests**

Run: `php artisan test`

Expected: all tests pass.

- [x] **Step 2: Run the frontend production build**

Run: `npm run build`

Expected: Vite build completes successfully.

- [x] **Step 3: Review the staged diff**

Run:

```bash
git diff --cached -- .gitignore public/storage
git status --short
```

Expected: only `.gitignore` and `public/storage` are staged for this implementation; pre-existing unrelated working-tree changes remain unstaged.
