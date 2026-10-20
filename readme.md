# Events for WordPress

Events is a WordPress plugin for publishing events, accepting bookings and managing payments. It is built for the block editor and provides both the public booking experience and the administration tools needed to run an event programme.

The plugin is not a fork of, nor compatible with, WP Events Manager. It has its own event model, REST API and Gutenberg blocks.

## Features

- Create and manage one-off events, locations, people, categories and tags
- Create recurring event series, manage individual occurrences and detach exceptions
- Browse events in an admin list or calendar view
- Duplicate an event to one or more dates
- Sell tickets with capacities, booking windows and availability tracking
- Manage bookings, attendees, notes, cancellations and exports
- Create custom booking and attendee forms
- Configure online and offline payment gateways, including SEPA payment QR codes
- Create and manage coupons
- Configure transactional email templates and booking notifications
- Build event pages with Gutenberg blocks, including booking, event details, upcoming events, featured events and programme PDF exports

## Requirements

- WordPress 7.1 or newer
- PHP 8.5 or newer
- PHP extensions: `curl` and `gd`

For a development checkout, Composer and Node.js/npm are also required.

## Installation

### Production

Install a release archive in WordPress as you would any other plugin, or place the release files in `wp-content/plugins/ctx-events/`. Activate **Events** in the WordPress plugins screen.

Release builds must include both `vendor/` and `build/`; they contain the PHP dependencies and compiled editor/admin assets.

### Development

Clone the repository into the WordPress plugins directory, then install and build its dependencies:

```bash
composer install
npm install
npm run build
```

For frontend development, run the asset watcher in a separate terminal:

```bash
npm start
```

## Getting started

1. Open **Events → Settings** and configure the general and payment settings needed for your site.
2. Add payment gateways under **Events → Gateways** when bookings require payment.
3. Create locations, people, forms or coupons as needed.
4. Create an event from **Events → New Event**, set its dates, booking settings and tickets, then publish it.
5. Add the **Event Booking** block and the desired event-detail blocks to the event template or page.

Recurring programmes are created under **Events → Recurring Events**. Individual occurrences can be cancelled or detached into regular events when they need their own details or booking settings.

## Gutenberg blocks

The plugin registers blocks from the `ctx-events` namespace. The main public blocks are:

- **Event Booking** — lets visitors select tickets, enter registration data and pay.
- **Event Details** and its detail blocks — show date, time, location, contact person, prices, audience, availability and booking deadline.
- **Upcoming Events** and **Events Table** — list forthcoming events.
- **Featured Event** — compose a highlighted event section from title, image, schedule, location, excerpt and button blocks.
- **Program PDF Export** — offers weekly, monthly or yearly programme PDFs.

The editor also includes blocks for location, person and coupon metadata, plus the form-field blocks used by custom forms.

## Development commands

```bash
composer test                 # Run the Pest test suite
composer analyse              # Run PHPStan
npm run ts:generate           # Generate TypeScript types from PHP resources
npm run build                 # Create production assets
npm start                     # Watch and rebuild development assets
npm run test:e2e              # Run Playwright end-to-end tests
```

## Architecture

The PHP code follows a domain-oriented, hexagonal structure:

- `includes/*/Domain` contains business entities, value objects and repository contracts.
- `includes/*/Application` contains use cases and application services.
- `includes/*/Infrastructure` contains WordPress persistence, integrations and framework adapters.
- `includes/*/Presentation` exposes REST endpoints and response resources.
- `src/` contains the TypeScript/React editor, admin and public booking applications.

Further notes are available in [docs/architecture.md](docs/architecture.md), [docs/domain/event.md](docs/domain/event.md) and [docs/booking.md](docs/booking.md).

## License

GPL-3.0. See the plugin header for the canonical license declaration.
