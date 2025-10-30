<?php

namespace Contexis\Events\Presentation\Admin;

final class AdminMenu implements AdminServiceInterface {
	public const MENU_SLUG = 'contexis_events_admin_menu';
	public string $hook = 'admin_menu';

	public function register(): void {
		add_menu_page(
			__('Events','events'),
			__('Events','events'),
			'manage_options',
			self::MENU_SLUG,
			fn() => print('<div id="contexis-events-admin-app"></div>'),
			'dashicons-calendar-alt',
			6
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Settings','events'),
			__('Settings','events'),
			'manage_options',
			'contexis_events_settings',
			fn() => print('<div id="contexis-events-settings-app"></div>')
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Bookings','events'),
			__('Bookings','events'),
			'manage_options',
			'contexis_events_bookings',
			fn() => print('<div id="contexis-events-bookings-app"></div>')
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Locations','events'),
			__('Locations','events'),
			'manage_options',
			'contexis_events_locations',
			fn() => print('<div id="contexis-events-locations-app"></div>')
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Persons','events'),
			__('Persons','events'),
			'manage_options',
			'contexis_events_persons',
			fn() => print('<div id="contexis-events-persons-app"></div>')
		);
	}
}