# Site Performance Optimization Plan

## 1. Background

Customers reported that the site feels slow or stuck. Current code review points to a high-risk interaction between global request middleware, homepage polling, request-time file I/O, and large client assets.

This plan focuses only on the high-priority risks found during the first investigation. It does not change product behavior unless explicitly stated.

## 2. Investigation Scope

Reviewed areas:

1. Web middleware registration.
2. Client environment detection and audit services.
3. Homepage summary polling.
4. Homepage controller and summary service.
5. Vite build output and homepage JavaScript loading.
6. Environment defaults that may affect production performance.

Important files:

1. `bootstrap/app.php`
2. `config/client_env.php`
3. `app/Modules/ClientEnv/Http/Middleware/ClientEnvProbeMiddleware.php`
4. `app/Modules/ClientEnv/Services/ClientEnvProbeLogService.php`
5. `app/Modules/ClientEnv/Services/ClientEnvDecisionAuditService.php`
6. `app/Modules/Home/Services/HomeSummaryService.php`
7. `resources/views/components/home/stats.blade.php`
8. `resources/js/app.js`
9. `resources/js/onchain-recharge-loader.js`

## 3. Main Findings

### 3.1 Global client environment middleware runs on almost every web request

`bootstrap/app.php` appends `ClientEnvProbeMiddleware` to the whole `web` middleware group.

Current effect:

1. Normal page views run environment detection.
2. JSON polling endpoints also run environment detection.
3. Static business flows such as homepage, product pages, recharge pages, and support pages all pay this cost unless excluded.

Risk:

1. Any expensive logic inside this middleware becomes a global latency tax.
2. High-frequency endpoints magnify this cost.

### 3.2 Probe log persistence is request-time full-file read and rewrite

`ClientEnvProbeLogService::appendUnique()` currently:

1. Reads the full probe log file.
2. Parses all entries.
3. Scans all entries for the current unique key.
4. Appends the new entry.
5. Rewrites the entire file.

Risk:

1. Runtime cost grows with log size.
2. Concurrent requests can contend on the same file.
3. Slow filesystem I/O directly blocks user requests.
4. The log file can become a production bottleneck under traffic.

Current local evidence:

1. `storage/app/private/client-env/probe-log.jsonl` exists.
2. Local size is about `103KB`; production can grow far beyond this.

### 3.3 Homepage summary polling runs every 3 seconds

`resources/views/components/home/stats.blade.php` calls `/home-summary` every 3 seconds.

The `/home-summary` endpoint calls `HomeSummaryService::resolve()`, which may:

1. Query `home_display_settings`.
2. Update `home_display_settings` when tick values advance.
3. Query popup campaign data for signed-in users.
4. Pass through global client environment middleware.
5. Possibly trigger probe persistence and decision audit.

Risk:

1. One open homepage tab continuously generates backend traffic.
2. Multiple users multiply request volume quickly.
3. Polling traffic can slow normal page loads because it competes for PHP workers and database/file I/O.

### 3.4 Client environment decision audit adds cache and database work

`ClientEnvDecisionAuditService::audit()` can:

1. Check cache for dedupe.
2. Write cache keys.
3. Insert rows into `client_env_decision_logs`.

Risk:

1. With `CACHE_STORE=file`, audit dedupe uses filesystem I/O.
2. Allow audit sampling can still create write traffic.
3. Deny decisions always write when audit is enabled.

### 3.5 Homepage can load large wallet-related JavaScript

`resources/js/app.js` imports `loadOnchainRechargeIfNeeded()`.

When selectors such as `#home-quick-pay-panel` exist, the page dynamically loads `resources/js/onchain-recharge.js` and wallet dependencies.

Build output evidence from `npm run build`:

1. `onchain-recharge-*.js`: about `269KB`, gzip about `99KB`.
2. Wallet-related chunks include larger files such as `core-*.js`, `dist-*.js`, and `w3m-modal-*.js`.
3. Total `public/build` size is about `3.1MB`.

Risk:

1. Mobile and weak-network users may feel slow first interaction.
2. Wallet code may load on homepage even when users do not intend to recharge.
3. Large JS parsing cost can cause visible main-thread jank.

### 3.6 Production environment may be using local-style settings

Local `.env` currently contains:

1. `APP_ENV=local`
2. `APP_DEBUG=true`
3. `CACHE_STORE=file`
4. `SESSION_DRIVER=file`
5. `CLIENT_ENV_DECISION_MODE=enforce`

Risk if similar settings are used in production:

1. Debug mode adds overhead and exposes errors.
2. File cache/session can increase I/O contention.
3. Enforced client environment decisions make middleware behavior critical to all requests.

## 4. Optimization Goals

1. Remove request-time full-file probe log read/rewrite from normal user traffic.
2. Reduce homepage polling pressure.
3. Keep client environment access rules functional without making every request slow.
4. Reduce unnecessary homepage JavaScript loading.
5. Confirm production uses optimized Laravel runtime settings.

## 5. Recommended Implementation Plan

### Phase 1: Make client environment logging cheap

Goal:

Stop `ClientEnvProbeLogService` from doing full-file read and rewrite during normal requests.

Recommended approach:

1. Disable probe file persistence in production by setting `CLIENT_ENV_MIDDLEWARE_PERSIST=false`.
2. Keep database decision audit for enforce/debug visibility, but tune sampling.
3. If raw probe persistence is still required, move it off the request path:
   - Use a queued job, or
   - Store probes in a database table with proper indexes, or
   - Use append-only JSONL without uniqueness scanning.

Preferred MVP change:

1. Add production `.env` guidance.
2. Keep middleware detection.
3. Disable request-time probe file persistence outside local/debug environments.

Tradeoff:

1. Disabling persistence reduces raw probe history.
2. Decision audit logs still preserve allow/deny evidence.

Verification:

1. Visit homepage and `/home-summary`.
2. Confirm `probe-log.jsonl` no longer grows in production mode.
3. Confirm client environment allow/deny behavior still works.

### Phase 2: Exclude high-frequency JSON endpoints from probe persistence or middleware

Goal:

Prevent polling endpoints from paying unnecessary environment probe cost.

Candidate excluded paths:

1. `home-summary`
2. `home-hero-panel`
3. `popup/*/shown`
4. `popup/*/dismiss`
5. `popup/*/confirm`

Recommended approach:

1. Add these paths to `client_env.middleware.excluded_paths` if client environment enforcement is not required for them.
2. If enforcement is required, split configuration into:
   - detection/enforcement paths
   - persistence paths
   - audit paths

Tradeoff:

1. Full exclusion reduces security checks on those endpoints.
2. Persistence-only exclusion keeps enforcement while reducing I/O.

Recommended decision:

Use persistence-only exclusion if endpoint access control must remain strict. Use full middleware exclusion only for endpoints that do not need client environment decisions.

Verification:

1. Run feature tests covering `/home-summary` and client-env enforcement.
2. Confirm denied access behavior remains correct for protected user-facing pages.
3. Confirm homepage polling no longer writes probe logs.

### Phase 3: Reduce homepage polling frequency and write behavior

Goal:

Cut backend request volume from idle homepage tabs.

Recommended changes:

1. Increase polling interval from `3000ms` to `15000ms` or `30000ms`.
2. Pause polling when the tab is hidden using `document.visibilityState`.
3. Avoid overlapping requests by skipping a poll while the previous request is still pending.
4. Consider returning `Cache-Control` headers if data can be briefly cached.
5. Consider moving summary tick updates to a scheduled command instead of writing during page/API requests.

Suggested MVP behavior:

1. Poll every `15000ms`.
2. Pause when hidden.
3. Prevent overlapping fetches.
4. Keep existing response shape.

Tradeoff:

1. Summary numbers update less frequently.
2. User-visible difference should be small because these values are decorative/live-display metrics.

Verification:

1. Open homepage and observe network panel.
2. Confirm only one `/home-summary` request every 15 seconds while visible.
3. Confirm no `/home-summary` requests are made while tab is hidden.
4. Confirm summary text and popup behavior still work.

### Phase 4: Defer wallet JavaScript until explicit user intent

Goal:

Avoid loading wallet/recharge code on homepage initial render unless the user opens or interacts with the quick-pay area.

Recommended changes:

1. Do not load `onchain-recharge.js` immediately just because `#home-quick-pay-panel` exists.
2. Load wallet code after an explicit user action, for example:
   - clicking quick pay
   - opening recharge panel
   - focusing wallet payment controls
3. Keep existing route/page behavior for dedicated recharge pages.

Tradeoff:

1. First wallet interaction may have a short loading state.
2. Initial homepage load becomes lighter for most visitors.

Verification:

1. Build with `npm run build`.
2. Open homepage and confirm wallet chunks are not fetched before interaction.
3. Click quick-pay entry and confirm wallet code loads and payment flow still initializes.

### Phase 5: Confirm production runtime settings

Goal:

Ensure Laravel is running with production-safe performance settings.

Required production settings:

1. `APP_ENV=production`
2. `APP_DEBUG=false`
3. `CACHE_STORE` should prefer database or Redis over file for production traffic.
4. `SESSION_DRIVER` should prefer database or Redis over file for production traffic.
5. Run Laravel optimization commands during deployment:
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
   - `php artisan event:cache` if events are used

Recommended if traffic grows:

1. Use Redis for cache/session/queue.
2. Keep database queue only for low-volume bootstrap environments.
3. Add Nginx/CDN caching headers for static assets.

Verification:

1. Check production `.env`.
2. Run `php artisan about` or equivalent deployment diagnostics.
3. Confirm config is cached.
4. Confirm `APP_DEBUG=false` in production.

## 6. Test Plan

Focused backend tests:

1. `php artisan test tests/Feature/...` for client environment enforcement paths.
2. Existing homepage feature tests, if present.
3. Add or update tests for excluded polling paths if middleware behavior changes.

Full verification before delivery:

1. `php artisan test`
2. `npm run build`
3. Manual homepage browser check.
4. Manual network-panel check for polling frequency and wallet chunk loading.

Production verification:

1. Compare response time before and after changes for `/`, `/home-summary`, `/products`.
2. Check PHP-FPM worker saturation.
3. Check database slow query log.
4. Check growth rate of `client_env_decision_logs`.
5. Check whether `probe-log.jsonl` stops growing or remains bounded.

## 7. Suggested Priority Order

1. Disable or remove request-time full-file probe persistence.
2. Reduce `/home-summary` polling pressure.
3. Exclude high-frequency endpoints from unnecessary probe/audit work.
4. Defer wallet JavaScript until user intent.
5. Confirm production environment optimization.
6. Add indexes only after slow query evidence confirms database bottlenecks.

## 8. Remaining Risks and Assumptions

Assumptions:

1. Customer-reported lag includes homepage usage.
2. Production behavior is close to the current code path.
3. Client environment enforcement is still required for user-facing pages.

Remaining unknowns:

1. Actual production response time per route.
2. Production PHP-FPM and database saturation.
3. Actual production size of `probe-log.jsonl`.
4. Whether production uses file cache/session.
5. Whether users report server latency, browser jank, or both.

Before making invasive changes, collect at least one production sample covering:

1. Top slow routes.
2. PHP request duration percentiles.
3. Slow SQL log.
4. Static asset waterfall for homepage.
5. Current environment/cache/session drivers.
