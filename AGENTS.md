# AGENTS.md

See `CLAUDE.md` for the full guide. Quick facts:

- **Purpose:** Salla OAuth 2.0 provider for `league/oauth2-client` (`salla/ouath2-merchant`, typo intended) plus Laravel glue: `SallaOauth` facade, `salla.oauth[:scopes]` bearer-token middleware (validated via `accounts.salla.sa/oauth2/user/info`, cached), and `salla-oauth` guard.
- **Stack:** PHP ^8.1 · league/oauth2-client ^2 · illuminate/support ^9–^13 · Orchestra Testbench · PHPUnit · phpcs (PSR-2).
- **Run/test:** `composer install && composer test` · `composer check` (PSR-2 lint, not run in CI; run it locally). CI matrix PHP 8.1/8.2/8.3 with Redis.
- **Default branch:** `master` (not `main`). Releases are manual semver tags (currently `3.0.x`).
- **Don't:** push to `master` · commit client secrets · rename the Composer package, guard (`salla-oauth`), middleware alias (`salla.oauth`), request attribute (`salla.oauth.user`) or cache payload shape without checking consumers (PartnerMarketing, DevelopersPortal, apps-marketplace-api, Experts, zatca-einvoicing, zoho-connect, Foodics-App) · log cache keys (they contain the raw bearer token).
- **Conventions:** PSR-4 under `Salla\OAuth2\Client\`; mock Salla by stubbing `fetchResourceOwnerDetails`; branch `feature/PROJ-123-desc`; PR title `feat(scope): desc`; Jira link required in PR body (full rules in `CLAUDE.md` → Conventions).
