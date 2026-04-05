# Project Rules (Engineering Layer)

## 1. Scope
1. These rules govern engineering implementation and code quality, not product strategy.
2. All development tasks must follow this document unless explicitly waived.

## 2. Framework First
1. Prefer Laravel official capabilities and recommended patterns first.
2. Priority order: Laravel official capability > official/recommended ecosystem package > mainstream maintained open-source package > custom implementation.
3. Add custom methods only when official and mature package options cannot satisfy requirements.
4. Before adding custom methods, document why official approaches are insufficient.
5. Do not rebuild existing framework capabilities (authorization, ORM, queue, cache, validation, etc.).

## 3. Locked Stack
1. Backend: Laravel 13 + Blade + Tailwind.
2. Runtime baseline: PHP 8.3, MySQL 5.7.
3. Authorization: Laravel `Policy/Gate`.
4. Auth (authentication) default: Laravel first-party solution (Breeze / starter kit) when needed.
5. Admin panel and follow-system packages are optional and require explicit approval before introduction.
6. Frontend UI component library (e.g. Flowbite) is optional and requires explicit approval before introduction.
7. Cache/queue drivers follow environment needs: use file/database for local bootstrap, introduce Redis when required by features or performance goals.

## 4. Layering Constraints
1. Controllers only handle input validation, service invocation, response mapping, and exception mapping.
2. Business workflows belong in Services.
3. Authorization logic belongs in Policy/Gate.
4. Complex query composition belongs in Query/Repository layer.
5. Blade views are presentation-only: no database querying and no complex business branching.

## 5. AuthN / AuthZ Boundaries
1. Public read pages can be anonymous (no authentication/authorization): homepage, public posts, tag pages, author pages.
2. Restricted read pages require authentication + authorization: VIP full content, admin pages.
3. All write endpoints require authentication.
4. Write endpoints with role/state restrictions must also pass authorization checks.

## 6. Data Access and Transactions
1. Multi-table writes must use transactions.
2. Critical tables must define required indexes and uniqueness constraints.
3. Prevent N+1 by explicit eager loading.
4. Configuration and constants belong in `config/*` or constant/enum classes, not magic strings.
5. Migrations must be repeatable and rollback-safe.

## 7. Security and Auditing
1. Mutating endpoints must enforce authentication and authorization.
2. Input must be validated via FormRequest or equivalent validation layer.
3. Output is escaped by default; rich text must be sanitized via allowlist.
4. Critical actions must be auditable (delete/ban/settlement/permission changes).

## 8. UI System Constraints
1. Frontend uses Flowbite + Tailwind only (no second frontend component system).
2. Admin backend uses Filament only (do not mix frontend component system into admin).
3. Design tokens (color/spacing/typography) should be centralized.
4. For user-facing pages, prefer Flowbite components/blocks first before custom UI implementation.
5. User-facing visual direction defaults to tech/crypto/AI style (clean, modern, data-centric, high contrast), while keeping readability and accessibility.

## 9. Testing and Quality Gate
1. Each new feature requires at least: success path, permission-denied path, and invalid-input path tests.
2. Minimum validation commands per change:
   - `php artisan test`
   - `npm run build`
3. Every frontend change (Blade/Tailwind/JS/CSS/Vite assets) must run `npm run build` before delivery.
4. Do not merge or deliver changes with failing tests/build.
5. Delivery notes must include: changed files, executed commands, verification result, known limitations.

## 10. Change Process
1. One change set should focus on one topic.
2. Before implementation, list change scope and affected files.
3. Do not introduce new dependencies, restructure directories, or alter data rules without explicit approval.
4. If requirements conflict, stop and ask before implementation.
5. Any database migration change (new/updated tables, columns, indexes, constraints) must synchronously update `database/sql/mvp_schema.sql` in the same change set.
6. New feature/component files must be grouped under a dedicated module-oriented directory as much as possible; avoid scattering them across unrelated locations.
7. If a change must touch other modules, keep cross-module intrusion minimal and limit edits to strictly necessary integration points.
8. From now on, new independent business features must default to `app/Modules/<Domain>` and corresponding module view/test directories; do not continue adding new independent business domains under root-level `app/*` directories unless the change is only a minimal extension of an existing historical root-level feature.
