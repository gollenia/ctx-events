# Changelog

All notable changes to this project are documented in this file.

## Unreleased

### Added

- npm commands for patch, minor, and major releases through the central
  `wp-github-release` CLI.

### Changed

- The calendar UI now uses German labels and appropriate date formats for all
  German WordPress locales.
- The GitHub updater dependency is configured through Composer as version
  `^0.4.0`.

## 0.1.12 - 2026-10-30

### Fixed

- Fixed frontend styles for the Events Table block.

## 0.1.11 - 2026-10-30

### Added

- Added German translations for email templates.

## 0.1.10 - 2026-10-30

### Changed

- Added the standalone Events Table block for tabular event lists, including
  configurable columns, filters, and table options.
- Added dedicated Featured Event pattern blocks.
- Widened the title column in the event overview.

### Fixed

- Fixed event list refreshes after cancellations.
- GitHub updater release downloads now use tags without a `v` prefix.
