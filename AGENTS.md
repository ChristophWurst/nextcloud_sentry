# AGENTS.md - Nextcloud Sentry Integration

## Project Overview

Nextcloud app that integrates Sentry error tracking. Captures unhandled exceptions
on the PHP server side and the JavaScript browser side. Also supports performance
monitoring (transactions/traces) for HTTP and WebDAV operations.

- **App ID:** `sentry`
- **PHP namespace:** `OCA\Sentry`
- **License:** AGPL-3.0-or-later

## Build Commands

### PHP (Composer)

```bash
composer install                    # Install all dependencies
composer install --no-dev -o        # Production install (optimized autoloader)
```

### JavaScript (npm + Webpack)

```bash
npm install                         # Install JS dependencies
npm run build                       # Production build (js/sentry.js)
npm run dev                         # Development build with watch mode
```

## Lint / Static Analysis

```bash
composer run lint                   # PHP syntax check (php -l on all .php files)
composer run psalm                  # Psalm static analysis (level 4, see psalm.xml)
```

There is no JS linter configured.

## Test Commands

### Unit Tests

```bash
composer run test:unit              # Run all unit tests (with coverage)
composer run test:unit:dev          # Run all unit tests (no coverage, faster)
```

### Integration Tests

```bash
composer run test:integration       # Run all integration tests (with coverage)
composer run test:integration:dev   # Run all integration tests (no coverage)
```

### Running a Single Test

```bash
# Single test file:
composer run test:unit tests/Unit/Reporter/SentryReporterAdapterTest.php

# Single test method:
composer run test:unit -- --filter testReportAnonymously tests/Unit/Reporter/SentryReporterAdapterTest.php

# Integration test (requires Nextcloud environment):
composer run test:integration:dev -- --filter testInitialization tests/Integration/InitializationTest.php
```

### Test Structure

- `tests/phpunit.unit.xml` - Unit test PHPUnit config
- `tests/phpunit.integration.xml` - Integration test PHPUnit config
- `tests/bootstrap.php` - Loads Nextcloud test framework + vendor autoloader
- `tests/Unit/` - Unit tests (fast, mocked dependencies)
- `tests/Integration/` - Integration tests (require running Nextcloud instance)
- Test framework: `christophwurst/nextcloud_testing` (provides `TestCase`, `ServiceMockObject`)

## Code Style Guidelines

### PHP

#### File Header

Every PHP file must start with:
1. `<?php` opening tag
2. Blank line
3. `declare(strict_types=1);`
4. Blank line
5. AGPL-3.0 license docblock with `@author` / `@copyright` tags

#### Imports

- Group imports: PHP/global classes first, then `OCA\Sentry\` (own namespace), then
  `OCP\` (Nextcloud API), then third-party (e.g. `Sentry\`).
- Sentry function imports use the `use function` syntax:
  `use function Sentry\init as initSentry;`
- Sort imports alphabetically within each group.

#### Classes

- One class per file, matching the filename.
- Opening brace on the same line as the class declaration:
  `class Foo extends Bar {`
- Constructor parameters: one per line when there are multiple, aligned with tabs.
- Use constructor property promotion sparingly; existing code uses explicit property
  declarations with assignment in constructor body.

#### Types and Properties

- All files use `declare(strict_types=1)`.
- Use PHP typed properties: `private IConfig $config;`, `private ?Transaction $transaction = null;`
- Use return type declarations on all methods.
- Use `?Type` for nullable types.
- Constants use `private const` (e.g., `private const levels = [...]`).
- Use `match` expressions where appropriate (see `Config.php`).

#### Naming Conventions

- **Classes:** PascalCase (`SentryReporterAdapter`, `RecursionAwareReporter`)
- **Methods:** camelCase (`getDsn`, `setSentryScope`, `beforeController`)
- **Properties:** camelCase (`$userSession`, `$minimumLogLevel`)
- **Constants:** camelCase for class constants (`levels`), UPPER_SNAKE_CASE not used
- **Interfaces:** prefixed with `I` (`ISentryReporter`, `IConfig`)
- **Test classes:** suffixed with `Test` (`SentryReporterAdapterTest`)
- **Test methods:** prefixed with `test` (`testReportAnonymously`)

#### Error Handling

- Catch specific exception types, not generic `\Exception` unless re-throwing.
- Use the RecursionAwareReporter decorator pattern to prevent infinite loops in
  error reporting code.
- The `report()` method silently returns for events below the minimum log level.
- Psalm suppressions are acceptable where needed: `/** @psalm-suppress TooManyArguments */`

#### Patterns

- **Nextcloud IBootstrap:** `Application` implements `IBootstrap` with `register()` and `boot()`.
- **Decorator pattern:** `RecursionAwareReporter` wraps `SentryReporterAdapter`.
- **DI via constructor injection:** All services receive dependencies through constructors,
  resolved by Nextcloud's DI container.
- **ServiceMockObject in tests:** Use `$this->createServiceMock(ClassName::class)` to
  auto-mock all constructor dependencies.
- **Static closures:** Use `static function` for closures that don't need `$this`
  (see `Application.php` registrations).

### JavaScript

- ES module syntax (`import`/`export`).
- No TypeScript; plain `.js` files.
- JSDoc comments for license headers.
- Global declarations at top of file: `/* global OC, oc_config */`
- Nextcloud libraries: `@nextcloud/auth`, `@nextcloud/initial-state`, `@nextcloud/logger`.
- Sentry initialized via `@sentry/browser`.
- Nullish coalescing assignment (`??=`) is used.
- Semicolons are used inconsistently (some lines have them, some don't). Follow the
  style of the surrounding code.

## Project Architecture

```
lib/
  AppInfo/Application.php          # Bootstrap: registers services, middleware, listeners
  Config.php                       # Reads sentry.* system config values
  Command/Test.php                 # occ sentry:test CLI command
  DAV/PerformanceMonitoringPlugin.php  # Sabre DAV plugin for WebDAV tracing
  Http/PerformanceMonitoringMiddleware.php  # HTTP middleware for request tracing
  InitialState/DsnProvider.php     # Passes public DSN to browser JS
  Listener/CustomCspListener.php   # Adds CSP headers for Sentry domain
  Reporter/
    ISentryReporter.php            # Tagging interface (extends ICollectBreadcrumbs, IMessageReporter)
    SentryReporterAdapter.php      # Main Sentry SDK integration
    RecursionAwareReporter.php     # Decorator preventing recursive reporting
  Helper/CredentialStoreHelper.php # Breaks recursion in credential fetching
src/
  init.js                          # Browser Sentry initialization
  logger.js                        # Logger using @nextcloud/logger
```

## CI/CD

GitHub Actions workflows (`.github/workflows/`):
- `test.yml` - PHPUnit unit + integration tests (PHP 8.1-8.5, Nextcloud stable32/33/master)
- `static-analysis.yml` - Psalm
- `lint.yml` - PHP syntax lint (PHP 8.0-8.5)
- `build.yml` - JS build (Node 16, 18)
- `conventional_commits.yml` - Enforces conventional commit format on PRs to master
- `release.yml` - Automated release via conventional-changelog
- `appstore-build-publish.yml` - Publishes to Nextcloud App Store on GitHub release

## Conventional Commits

This project enforces conventional commit messages. Use the format:

```
feat: add new feature
fix: correct bug in reporter
chore: update dependencies
```
