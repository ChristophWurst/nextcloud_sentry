# Changelog
All notable changes to this project will be documented in this file.

## 8.0.0 – 2021-07-02
### Added
- Nextcloud 21-23 support
- PHP8.0 support
### Changed
- Updated dependencies
### Removed
- Nextcloud 18-20 support
- PHP7.3 support

## 7.0.0 – 2020-08-26
### Added
- Nextcloud 20 support
### Changed
- Updated dependencies
### Removed
- Nextcloud 17 support

## 6.2.2 – 2020-03-19
### Changed
- Updated dependencies

## 6.2.1 – 2020-02-10
### Fixed
- Problem with Curl php package

## 6.2.0 – 2020-02-03
### Added
- Nextcloud 19 support
### Changed
- Updated Sentry SDK
### Fixed
- Dependency conflict with other Guzzle instances
- Recursive reports, e.g. with LDAP

## 6.1.1 – 2019-12-10
- Downgraded Sentry php SDK due to conflict with Nextcloud's Guzzle client

## 6.1.0 - 2020-01-10
### Changed
- Updated Sentry SDKs

## 6.0.3 – 2020-01-07
### Changed
- New and updated translations
- Updated dependencies

## 6.0.2 – 2019-12-12
### Fixed
- JavaScript vulnerability in `serialize-javascript` dependency

## 6.0.1 – 2019-12-10
### Changed
- New Sentry SDKs
### Fixed
- Application class instantiation warning

## 6.0.0 – 2019-12-04
### Added
- php7.4 support
- Nextcloud 18 support
### Changed
- New Sentry JavaScript SDK
### Fixed
- App name as tag regression
### Removed
- php7.1 support

## 5.2.0 – 2019-12-04
### Changed
- New Sentry JavaScript SDK
- Updated dependencies

## 5.1.0 – 2019-08-26
### Added
- Ability to report messages
- Ability to report CSP violations (see admin docs for configuration)
### Changed
- New Sentry SDKs

## 5.0.0 – 2019-08-07
### Added
- Nextcloud 17 support
- Faster loading of the client-side config
### Changed
- New Sentry SDKs
### Fixed
- Update vulnerable `lodash` dependency
### Removed
- Nextcloud 15 support
- php7.0 support

## 4.0.1 – 2019-02-12
### Changed
- Update some dependencies
### Fixed
- Update vulnerable `lodash` dependency

## 4.0.0 – 2018-12-12
### Added
- Test command now includes breadcrumbs
- Nextcloud 16 support
### Changed
- Dropped Nextcloud 14 support
### Fixed
- JavaScript reports

## 3.4.1 – 2018-11-13
### Fixed
- Infinite recursion during an error report

## 3.4.0 – 2018-11-09
### Added
- Breadcrumb support
- Nextcloud 15 support
- Set username (loginname) if possible
### Fixed
- Loading of public DSN for custom app directories

## 3.3.0 – 2018-08-20
### Added
- Tagging of the affected app if it's known by the context
### Changed
- Updated Sentry's PHP library

## 3.2.0 – 2018-08-08
### Added
- Release version tagging for JavaScript reports

## 3.1.0 – 2018-08-07
### Added
- Test command `occ sentry:test` to test the configuration

## 3.0.0 – 2018-08-02
### Changed
- Dropped Nextcloud 13 support
- Requires php7.0+
- Updated Sentry library

## 2.0.1 - 2018-05-16
### Fixed
- JavaScript exception reporting on public pages

## 2.0.0 - 2018-05-15
### Added
- JavaScript exception reporting (see admin docs for configuration)

## 1.2.0 - 2018-05-14
### Changed
- Updated Sentry SDK

## 1.1.0 - 2017-12-12
### Added
- Configurable log level to filter out warnings
- Php7.2 support
- Admin documentation
### Fixed
- Better app description
