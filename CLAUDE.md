# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run all tests
composer test

# Run a single test file
vendor/bin/pest tests/Feature/KPayClientTest.php

# Run a specific test by description
vendor/bin/pest --filter "sends a pay request"

# Fix code style (PSR-12 preset)
vendor/bin/pint

# Dry-run style checks without fixing
vendor/bin/pint --test

# Run Rector (static analysis / automated refactoring)
vendor/bin/rector process --dry-run
vendor/bin/rector process
```

## Architecture

This is a **Laravel package** (not a standalone app) targeting Laravel 12 / PHP 8.2+. It is tested via [Orchestra Testbench](https://github.com/orchestral/testbench), which bootstraps a minimal Laravel app in tests without needing a full Laravel installation.

### Package wiring

- `KPayServiceProvider` registers `KPayClient` as a singleton (aliased as `kpay`) and conditionally loads the callback route.
- `Facades/KPay.php` proxies to the `kpay` alias so callers can use `KPay::pay(...)` and `KPay::checkStatus(...)`.
- The service provider is auto-discovered via `extra.laravel` in `composer.json`.

### Payment flow

1. **Initiation** — `KPayClient::pay(array $payload)` POSTs to the K-Pay base URL with `action=pay`. Defaults (`currency`, `retailerid`, `returl`, `redirecturl`, `logourl`) are merged from config so callers only need to supply per-request fields.
2. **Auth** — every request sends `Kpay-Key` header and `Authorization: Basic base64(username:password)`.
3. **Status check** — `KPayClient::checkStatus(string $refid)` POSTs with `action=checkstatus`. Status codes: `01` = success, `02` = failed, `03` = pending.
4. **Webhook** — `POST /kpay/callback` is handled by `KPayCallbackController`, which dispatches one of three events (`PaymentSucceeded`, `PaymentFailed`, `PaymentPending`) based on `statusid`, then returns `{"tid":"...","refid":"...","reply":"OK"}` as required by the K-Pay API.

### Configuration (`config/kpay.php`)

All config keys live under the `kpay.*` namespace and map to env vars: `KPAY_BASE_URL`, `KPAY_API_KEY`, `KPAY_USERNAME`, `KPAY_PASSWORD`, `KPAY_RETAILER_ID`, `KPAY_RETURL`, `KPAY_REDIRECTURL`, `KPAY_CURRENCY`, `KPAY_LOGOURL`, `KPAY_CALLBACK_ENABLED`, `KPAY_CALLBACK_PATH`, `KPAY_CALLBACK_MIDDLEWARE`.

### Testing conventions

- Tests use **Pest** with the `TestCase` base class in `tests/TestCase.php`, which extends Orchestra's `TestCase` and sets all required config via `defineEnvironment()`.
- HTTP calls are faked with `Http::fake()`; no real network calls are made in tests.
- CI runs on PHP 8.2 and 8.3.

### Commit conventions

Commits must follow [Conventional Commits](https://www.conventionalcommits.org/) (enforced by the `commit-convention` CI workflow). Use prefixes like `feat:`, `fix:`, `chore:`, `refactor:`, `test:`, `docs:`.
