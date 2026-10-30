# Changelog

All notable changes to this project are documented in this file.

## Unreleased

## 0.1.14 - 2026-10-30

### Added

- npm commands for patch, minor, and major releases through the central
  `wp-github-release` CLI.

### Changed

- The calendar UI now uses German labels and appropriate date formats for all
  German WordPress locales.
- The GitHub updater dependency is configured through Composer as version
  `^0.4.0`.
- Extracted calendar modal actions into their own component.

### Fixed

- Reduced duplicate calendar requests for the same date range to one request.
- Simplified the calendar event overlap query now that all events have an end
  date, substantially improving calendar response times.

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
