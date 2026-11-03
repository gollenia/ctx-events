<?php
declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress\Admin;

use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Event\Infrastructure\EventTaxonomy;
use Contexis\Events\Event\Infrastructure\RecurringEventPost;
use Contexis\Events\Form\Infrastructure\AttendeeFormPost;
use Contexis\Events\Form\Infrastructure\BookingFormPost;
use Contexis\Events\Location\Infrastructure\LocationPost;
use Contexis\Events\Payment\Infrastructure\CouponPost;
use Contexis\Events\Person\Infrastructure\PersonPost;
use Contexis\Events\Shared\Presentation\Contracts\AdminService;

final class ReactAdminNoticeSuppressor implements AdminService
{
	/** @var list<string> */
	private const REACT_PAGE_SLUGS = [
		AdminMenu::MENU_SLUG,
		'contexis_events_settings',
		'contexis_events_bookings',
		'contexis_events_recurring',
		'contexis_events_coupons',
		'contexis_events_forms',
		'contexis_events_email',
		'contexis_events_gateways',
	];

	/** @var list<string> */
	private const PLUGIN_POST_TYPES = [
		EventPost::POST_TYPE,
		RecurringEventPost::POST_TYPE,
		LocationPost::POST_TYPE,
		PersonPost::POST_TYPE,
		CouponPost::POST_TYPE,
		BookingFormPost::POST_TYPE,
		AttendeeFormPost::POST_TYPE,
	];

	/** @var list<string> */
	private const PLUGIN_TAXONOMIES = [
		EventTaxonomy::CATEGORIES,
		EventTaxonomy::TAGS,
		PersonPost::CATEGORY,
		BookingFormPost::TAGS,
	];

	public function hook(): void
	{
		add_action('in_admin_header', [$this, 'suppressNotices'], PHP_INT_MAX);
	}

	public function suppressNotices(): void
	{
		if (!$this->isPluginAdminPage()) {
			return;
		}

		remove_all_actions('all_admin_notices');
		remove_all_actions('admin_notices');
	}

	private function isPluginAdminPage(): bool
	{
		$screen = get_current_screen();
		if ($screen instanceof \WP_Screen && (
			in_array($screen->post_type, self::PLUGIN_POST_TYPES, true)
			|| in_array($screen->taxonomy, self::PLUGIN_TAXONOMIES, true)
		)) {
			return true;
		}

		global $pagenow;
		if ($pagenow !== 'admin.php') {
			return false;
		}

		$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

		return in_array($page, self::REACT_PAGE_SLUGS, true);
	}
}
