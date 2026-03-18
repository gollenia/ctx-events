<?php

declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress\Admin;

final class AdminMenu implements AdminServiceInterface
{
    public const MENU_SLUG = 'ctx_events_admin_menu';
    public string $hook = 'admin_menu';

    public function hook(): void
    {
        add_action($this->hook, [$this, 'register']);
        add_filter('parent_file', [$this, 'parent_file']);
    }

    public function parent_file(string $parent_file): string
    {
        global $current_screen;
        if ($current_screen && $current_screen->post_type === 'ctx-event') {
            return AdminMenu::MENU_SLUG;
        }

        return $parent_file;
    }

    public function register(): void
    {
        add_menu_page(
            __('Events', 'ctx-events'),
            __('Events', 'ctx-events'),
            'manage_options',
            self::MENU_SLUG,
            [$this, 'eventsPage'],
            'dashicons-calendar-alt',
            6
        );

        add_submenu_page(
            self::MENU_SLUG,
            __('Events', 'ctx-events'),
            __('Events', 'ctx-events'),
            'manage_options',
            self::MENU_SLUG,
            [$this, 'eventsPage'],
            0
        );

        add_submenu_page(
            self::MENU_SLUG,
            __('Settings', 'ctx-events'),
            __('Settings', 'ctx-events'),
            'manage_options',
            'contexis_events_settings',
            function () {
                echo '<div id="ctx-options-admin"></div>';
            }
        );

        add_submenu_page(
            self::MENU_SLUG,
            __('Bookings', 'ctx-events'),
            __('Bookings', 'ctx-events'),
            'manage_options',
            'contexis_events_bookings',
            fn() => print('<div id="ctx-bookings-admin"></div>')
        );

		add_submenu_page(
			self::MENU_SLUG,
			__('Forms', 'ctx-events'),
			__('Forms', 'ctx-events'),
			'manage_options',
			'contexis_events_forms',
			fn() => print('<div id="ctx-forms-admin"></div>')
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Email', 'ctx-events'),
			__('Email', 'ctx-events'),
			'manage_options',
			'contexis_events_email',
			fn() => print('<div id="ctx-email-admin"></div>')
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Gateways', 'ctx-events'),
			__('Gateways', 'ctx-events'),
			'manage_options',
			'contexis_events_gateways',
			fn() => print('<div id="ctx-gateways-admin"></div>')
		);
    }

    public function eventsPage(): string
    {
		echo "<div id='ctx-events-list'></div>";
        return '';
    }

	
}
