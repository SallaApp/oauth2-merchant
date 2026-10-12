# oauth2-merchant (`salla/ouath2-merchant`)

Salla OAuth 2.0 client provider for [`league/oauth2-client`](https://github.com/thephpleague/oauth2-client),
plus a Laravel integration: a `SallaOauth` facade/singleton for the authorization-code and refresh-token flows
against `accounts.salla.sa`, a `salla.oauth` route middleware that validates a merchant's bearer token, and a
`salla-oauth` auth guard that exposes the resolved merchant user.

## Stack
- PHP `^8.1|^8.3` · `league/oauth2-client ^2.0` · `illuminate/support ^9–^13`
- Dev: `laravel/framework`, `orchestra/testbench ^6–^11`, `phpunit/phpunit ^8–^11`, `squizlabs/php_codesniffer`
- Public dependencies only (no private registry needed)

## Run / test / build
```bash
composer install
composer test                                   # = phpunit, suite ./test (config in phpunit.xml)
./vendor/bin/phpunit --filter OauthMiddlewareTest
composer check                                  # = phpcs src --standard=psr2 -sp
```
CI (`.github/workflows/unit-test.yaml`) runs PHPUnit on PHP 8.1 (PHPUnit 10.5), 8.2 and 8.3 (PHPUnit 11.5) with a
Redis service, then uploads coverage to Codacy. It runs on every PR and on `master` pushes.

## Architecture
- `src/Provider/Salla.php` — `AbstractProvider` subclass. Endpoints `{base_url}/oauth2/auth`, `/oauth2/token`,
  `/oauth2/user/info`; `setBaseUrl()`, `setHeaders()`; scope separator `,`; `checkResponse()` throws
  `IdentityProviderException` from `error.message` / `error_description`; `fetchResource()` for authenticated calls.
- `src/Provider/SallaUser.php` — resource owner built from the `user/info` response (`data.*`): `getId()`, `getName()`,
  `getEmail()`, `getMobile()`, `getRole()`, `getStoreId()` and the other `getStore*()` getters (from `data.merchant`),
  `getExpiredAt()` (`data.context.exp`), `getScope()` (`data.context.scope`, space-separated), `toArray()` (`data`).
- `src/ServiceProvider.php` — auto-discovered. Merges `config/salla-oauth.php`, binds the `Contracts\SallaOauth`
  singleton (a configured `Salla` provider), registers the `salla-oauth` guard config (`driver: salla-oauth`, a
  `RequestGuard` over `Auth/AuthRequest`), and aliases the `salla.oauth` middleware. Config publish tag: `salla-oauth`.
- `src/Http/OauthMiddleware.php` — `salla.oauth[:scope1,scope2]`. Requires a bearer token (else 401), looks it up in
  cache (`{cache-prefix}.{token}`, optionally tagged), otherwise calls `user/info` (any exception → 401). Scope check
  passes if the token has **any** listed scope. Caches `['data' => …]` until the token's `exp` and stores the user
  on the request attribute `salla.oauth.user`.
- `src/Auth/AuthRequest.php` — guard callback; wraps the request attribute in `Models/OAuthUser`.
- `src/Models/OAuthUser.php` — `Authenticatable`; `getAuthIdentifier()` is the Salla user id; magic `__get` reads
  from `data` (e.g. `->merchant`, `->context`); password/remember-token methods throw `BadMethodCallException`.
- `src/Facade/SallaOauth.php` — facade over `Contracts\SallaOauth` (empty marker interface; the bound class is `Provider\Salla`).
- `config/salla-oauth.php` — `SALLA_OAUTH_CLIENT_ID`, `SALLA_OAUTH_CLIENT_SECRET`, `SALLA_OAUTH_CLIENT_REDIRECT_URI`,
  `SALLA_OAUTH_BASE_URL` (default `https://accounts.salla.sa`), `SALLA_OAUTH_PREFIX_CACHE` (`oauth`), `SALLA_OAUTH_CACHE_TAG` (empty).
- Namespace `Salla\OAuth2\Client\` → `src/`; tests `Salla\OAuth2\Client\Test\` → `test/` (Orchestra Testbench).

## Cross-repo
- **Provides:** `salla/ouath2-merchant` (Composer name keeps the `ouath2` typo; repo is `SallaApp/oauth2-merchant`)
- **Depends on:** no internal `salla/*` packages
- **Used by:** `PartnerMarketing` (`~3.0`), `DevelopersPortal` (`^3.0`), `apps-marketplace-api` (`~2.0`), `Experts`
  (`^2.0`), `zatca-einvoicing` / `zatca-with-filament` / `zoho-connect` (`^1.4`), `Foodics-App` (`^1.0`) — **check
  before changing the middleware contract, request attribute name, guard name, cache format, or `SallaUser` getters.**
- Consumers are spread across three major lines (1.x/2.x/3.x); a breaking change only reaches those that bump the constraint.

## Conventions
- Default branch is **`master`** (not `main`). Branch `feature/PROJ-123-desc`; PR title `feat(scope): desc` (checked
  by `lint-pr.yaml`); Jira link in the PR body; fill `.github/PULL_REQUEST_TEMPLATE/pull_request_template.md`.
- Releases are manual semver tags (currently `3.0.x`); breaking changes need a major tag.
- Lint standard is **PSR-2 only** (`composer check` = `phpcs src --standard=psr2`, no phpcs config file). CI does **not** run it (`unit-test.yaml` only runs PHPUnit), so run `composer check` locally before pushing.
- New Laravel versions are added by widening `illuminate/support`, `orchestra/testbench`, and the CI matrix together
  (see `feat(CPD-31905): Support Laravel 13`).
- Middleware/guard changes need tests in `test/OauthMiddlewareTest.php`; external calls are mocked by stubbing
  `Salla::fetchResourceOwnerDetails` (see `setupMockSalla()`).
- Branch prefixes are `feature/`, `bugfix/`, `hotfix/`, or `test/` plus the Jira key. The PR title scope is mandatory, PRs open as drafts, and secrets live in the consumer's env/Doppler, never in git.

## Gotchas
- **Expired tokens are accepted:** since `fix(CPD-31891)` (#43) the middleware no longer rejects a token whose
  `context.exp` is in the past, and the cache TTL is the **absolute** distance to `exp` — an already-expired token is
  cached for as long as it has been expired. Validity is whatever `accounts.salla.sa/oauth2/user/info` returns.
- **Cache key contains the raw bearer token** (`{prefix}.{token}`); never log or expose cache keys. With a tag
  configured, the tag is only used when the cache store supports tags — otherwise it silently falls back to untagged.
- **Scopes are any-of:** `salla.oauth:orders.read,orders.read_write` passes with either scope; no-arg middleware skips scope checks.
- **Auth ≠ authorization:** the guard only proves a valid Salla merchant token. Store/permission checks are the consumer's job.
- **README drift:** it shows `auth()->guard('salla-oauth')->merchant()`, which doesn't exist on `RequestGuard`; use
  `auth()->guard('salla-oauth')->user()->merchant`, which returns the raw `data.merchant` **array** through `OAuthUser::__get`, so read it as `['id']`. Or read `request()->attributes->get('salla.oauth.user')`, which is the **`SallaUser` object**: use `->getStoreId()` and the other getters, or `->toArray()['merchant']`. The two values have different shapes, so don't mix their access styles. `CHANGELOG.md` stops at 1.0.0.
- **Two scope separators:** authorization URLs join scopes with `,` (`getScopeSeparator`), while the token's
  `context.scope` is space-separated.
- **Package name typo:** `salla/ouath2-merchant` — do not "fix" it in `composer.json`; every consumer requires that name.
- No CODEOWNERS file; secrets (`SALLA_OAUTH_CLIENT_SECRET`) come from the consumer's env/Doppler only.
