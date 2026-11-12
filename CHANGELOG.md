# Changelog

All notable changes to this project are documented in this file.

## Unreleased

## 0.4.1 - 2026-11-12

### Fixed

- Restore event images and other details in recurring-enabled event lists.
- Avoid errors when an event's location, person or image is missing.
- Apply the selected Upcoming card column count at every screen size.

## 0.4.0 - 2026-11-12

### Added

- Validate attendee age limits using a marked date field in the attendee form.
- Add richer formatting options to email templates, including tables and colour boxes.
- Allow the Upcoming Events card view to use one to four columns on large screens.

### Changed

- Include recurring occurrences in Upcoming Events and Events Table blocks by default.

### Fixed

- Prevent browser link styling and duplicate dates in Events Table title cells.

## 0.3.0 - 2026-11-10

### Added

- Support multi-day events that occur at the same hours on every day of their
  date range.

### Changed

- Replace the ineffective all-day event toggle with an explicit daily-event
  setting.
- Format daily event times as "Each day …" in event details and block
  bindings.

### Fixed

- Exclude draft events from the public Upcoming Events and Upcoming Events
  Table blocks, including for logged-in users.

## 0.2.5 - 2026-11-10

### Fixed

- Regenerate WordPress rewrite rules for event URLs after installation, updates,
  and event-slug changes.
- Clearly label payment gateways as configured or not configured for activation.

## 0.2.4 - 2026-11-10

### Added

- Allow setting a shared title while duplicating events.

### Changed

- Reworked the event-duplication dialog as a compact table with directly
  editable date fields.

### Fixed

- Persist Mollie live and test API keys from the gateway form and correctly
  show the selected live/test operating mode.

## 0.2.3 - 2026-11-10

### Fixed

- Load block-editor script translations from the plugin language directory so
  translated block titles remain visible in the editor.

## 0.2.2 - 2026-11-10

### Changed

- Replaced the event calendar header with WordPress components: native date
  input, view selector, navigation controls, and a responsive three-part
  layout.
- Added text domains to all block metadata and refreshed German translations.
- Improved calendar affordances with hover feedback for days and pointer
  cursors for clickable events.

## 0.2.1 - 2026-11-10

### Added

- Display draft events in the admin calendar with a distinct, subdued style.

### Changed

- Allow editors to access the event and recurring-event administration areas.
- Refined the event calendar styling.

## 0.2.0 - 2026-11-03

### Added

- Create and automatically email additional payment links when a booking's
  price increases after attendees are added or changed.

### Changed

- Treat a booking as paid only when the sum of its successful transactions
  covers its current total, while retaining approved bookings during an open
  additional-payment request.
- Keep already paid transactions when bookings or attendees are cancelled;
  refunds remain an explicit external process.

### Fixed

- Reconcile open payment requests with booking price reductions and cancel
  obsolete Mollie payment requests instead of leaving them payable.

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
