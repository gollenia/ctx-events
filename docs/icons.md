# Icon Registry

## Purpose

The plugin exposes a central `ctx_icons` registry for semantic icons.

It is designed so that:

- this plugin can register its default icons
- other Contexis plugins can register additional icons
- themes can override existing icons intentionally
- PHP and React consume the same resolved icon map

The registry is infrastructure, not domain logic. The implementation lives in:

- `includes/Shared/Infrastructure/Icons/IconRegistry.php`
- `includes/Shared/Infrastructure/Icons/IconRenderer.php`
- `includes/Shared/Infrastructure/Icons/IconRegistryBootstrap.php`

Event-specific defaults are registered in:

- `includes/Event/Infrastructure/RegisterEventIcons.php`

## Concepts

Icons are addressed by semantic names such as:

- `date`
- `location`
- `time`
- `price`
- `speaker`
- `phone`
- `email`

Consumers should use semantic names instead of icon-library-specific names.

## Lifecycle

The registry is booted once during plugin startup.

Registration happens in this order:

1. `do_action( 'ctx_icons_register', $registry )`
2. `do_action( 'ctx_icons_override', $registry )`
3. `apply_filters( 'ctx_icons', $icons, $registry )`

This gives a clear separation between normal registration and intentional replacement.

## Registration API

### Register

Use `register()` for normal icon registration.

- first write wins
- if an icon name already exists, the new registration is ignored

```php
add_action('ctx_icons_register', static function ($registry): void {
    $registry->register('phone', '<svg ...>...</svg>', 'my-plugin');
});
```

Use `registerFromFile()` when the icon already exists as an SVG file.

```php
add_action('ctx_icons_register', static function ($registry): void {
    $registry->registerFromFile(
        'date',
        get_stylesheet_directory() . '/icons/date.svg',
        'my-plugin'
    );
});
```

### Override

Use `override()` when replacing an existing icon is intentional.

```php
add_action('ctx_icons_override', static function ($registry): void {
    $registry->override('date', '<svg ...>...</svg>', 'my-theme');
});
```

For file-based overrides:

```php
add_action('ctx_icons_override', static function ($registry): void {
    $registry->overrideFromFile(
        'date',
        get_stylesheet_directory() . '/icons/date.svg',
        'my-theme'
    );
});
```

## Naming

Prefer stable semantic names.

Good examples:

- `date`
- `booking_closed`
- `speaker`
- `warning`

Avoid names tied to a specific icon library.

Avoid:

- `calendar_today`
- `more_horiz`
- `material_event`

All shared icon packs should use the same semantic names. If two packs mean the same concept, they should register the same name.

## PHP Usage

PHP should render icons through the shared renderer, not by reading SVG files directly.

Example:

```php
$html = $iconRenderer->render('date');
```

In the event frontend, `BlockEventLoader::renderIcon()` is the current convenience wrapper around the shared renderer.

## React Usage

React consumes the resolved icon map from `window.ctxIcons`.

The shared component is:

- `src/shared/icons/EventIcon.tsx`

Example:

```tsx
<EventIcon name="date" />
```

The editor receives its icon map directly from PHP in `Assets.php`.

For frontend apps, the event plugin exposes:

- `GET /wp-json/events/v3/icons`

It can return all event icons or only a subset via `?names=date,location`.

## Extension Guidelines

If another plugin wants to contribute icons:

- register new semantic names in `ctx_icons_register`
- do not override existing names unless there is a strong reason

If a theme wants to replace the visual language:

- use `ctx_icons_override`
- override only the semantic names it wants to control

If multiple plugins try to register the same semantic icon:

- the first registration wins
- later plugins should pick another semantic name unless they truly mean the same concept
