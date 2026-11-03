# Changelog

All notable changes to this project are documented in this file.

## Unreleased

## 0.1.19 - 2026-11-03

### Changed

- Simplified transaction history in the booking editor to the payment amount,
  status, payment type, and transaction ID or booking reference.
- Clarified activity-log colours: created and approved bookings are green;
  cancellations and rejected bookings are red.

### Fixed

- Made DataTable selection columns explicit and corrected empty-table spans for
  tables with an enabled selection column.

## 0.1.18 - 2026-11-03

### Added

- Added an admin action to send a stable payment link for pending online
  bookings, creating a replacement payment when the previous one expired.

### Changed

- Removed post-booking payment-gateway changes to prevent pending payments
  from being assigned to the wrong payment method.

### Fixed

- Send Mollie's classic payment webhook URL in addition to configured Next-gen
  webhooks, validate the API key for the selected mode, and recheck online
  payments before marking them expired.

## 0.1.17 - 2026-11-03

### Changed

- Hide WordPress admin notices on ctx-events administration screens.

## 0.1.16 - 2026-11-03

### Changed

- Improved event calendar actions and cancellation handling.

### Fixed

- Duplicated events retain their original title and are published immediately.

## 0.1.15 - 2026-10-30

### Changed

- Updated the GitHub updater integration.

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
