<?php

declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress\Admin;

use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Infrastructure\BookingMigration;
use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Event\Infrastructure\EventTaxonomy;

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
        if (
            $current_screen &&
            (
                $current_screen->post_type === EventPost::POST_TYPE ||
                $current_screen->taxonomy === EventTaxonomy::CATEGORIES ||
                $current_screen->taxonomy === EventTaxonomy::TAGS
            )
        ) {
            return AdminMenu::MENU_SLUG;
        }

        return $parent_file;
    }

    public function register(): void
    {
        $pendingBookings = $this->pendingBookingCount();

        add_menu_page(
            __('Events', 'ctx-events'),
            $this->withCounter(__('Events', 'ctx-events'), $pendingBookings),
            'manage_options',
            self::MENU_SLUG,
            [$this, 'eventsPage'],
            'data:image/svg+xml;base64,CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgLTk2MCA5NjAgOTYwIiB3aWR0aD0iMjRweCIgZmlsbD0iIzFmMWYxZiI+PHBhdGggZD0iTTUwOS0yNjlxLTI5LTI5LTI5LTcxdDI5LTcxcTI5LTI5IDcxLTI5dDcxIDI5cTI5IDI5IDI5IDcxdC0yOSA3MXEtMjkgMjktNzEgMjl0LTcxLTI5Wk0yMDAtODBxLTMzIDAtNTYuNS0yMy41VDEyMC0xNjB2LTU2MHEwLTMzIDIzLjUtNTYuNVQyMDAtODAwaDQwdi04MGg4MHY4MGgzMjB2LTgwaDgwdjgwaDQwcTMzIDAgNTYuNSAyMy41VDg0MC03MjB2NTYwcTAgMzMtMjMuNSA1Ni41VDc2MC04MEgyMDBabTAtODBoNTYwdi00MDBIMjAwdjQwMFptMC00ODBoNTYwdi04MEgyMDB2ODBabTAgMHYtODAgODBaIi8+PC9zdmc+',
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
            $this->withCounter(__('Bookings', 'ctx-events'), $pendingBookings),
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
			__('Categories', 'ctx-events'),
			__('Categories', 'ctx-events'),
			'manage_categories',
			'edit-tags.php?taxonomy=' . EventTaxonomy::CATEGORIES . '&post_type=' . EventPost::POST_TYPE
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Tags', 'ctx-events'),
			__('Tags', 'ctx-events'),
			'manage_categories',
			'edit-tags.php?taxonomy=' . EventTaxonomy::TAGS . '&post_type=' . EventPost::POST_TYPE
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

    private function pendingBookingCount(): int
    {
        global $wpdb;

        $table = BookingMigration::getTableName();
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE status = %d",
            BookingStatus::PENDING->value
        );

        return (int) $wpdb->get_var($sql);
    }

    private function withCounter(string $label, int $count): string
    {
        if ($count < 1) {
            return $label;
        }

        $countLabel = number_format_i18n($count);

        return sprintf(
            '%s <span class="awaiting-mod"><span class="pending-count">%s</span></span>',
            esc_html($label),
            esc_html($countLabel)
        );
    }

	
}
