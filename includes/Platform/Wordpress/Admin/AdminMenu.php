<?php

namespace Contexis\Events\Platform\Wordpress\Admin;

use Mpdf\Tag\A;

final class AdminMenu implements AdminServiceInterface
{
    public const MENU_SLUG = 'contexis_events_admin_menu';
    public string $hook = 'admin_menu';

    public function hook(): void
    {
        add_action($this->hook, [$this, 'register']);
    }


    public function register(): void
    {
        add_menu_page(
            __('Events', 'events'),
            __('Events', 'events'),
            'manage_options',
            self::MENU_SLUG,
            [$this, 'settingsPage'],
            'dashicons-calendar-alt',
            6
        );

        add_submenu_page(
            'contexis_events_admin_menu',
            __('Settings', 'events'),
            __('Settings', 'events'),
            'manage_options',
            'contexis_events_settings',
            function () {
                echo '<div id="ctx-options-admin"></div>';
            }
        );

        add_submenu_page(
            self::MENU_SLUG,
            __('Bookings', 'events'),
            __('Bookings', 'events'),
            'manage_options',
            'contexis_events_bookings',
            fn() => print('<div id="contexis-events-bookings-app"></div>')
        );

        add_submenu_page(
            self::MENU_SLUG,
            __('Locations', 'events'),
            __('Locations', 'events'),
            'manage_options',
            'contexis_events_locations',
            fn() => print('<div id="contexis-events-locations-app"></div>')
        );

        add_submenu_page(
            self::MENU_SLUG,
            __('Persons', 'events'),
            __('Persons', 'events'),
            'manage_options',
            'contexis_events_persons',
            fn() => print('<div id="contexis-events-persons-app"></div>')
        );
    }

    public function settingsPage(): string
    {
        echo "<h1>Settings Page</h1>";
        return '';
    }
}
