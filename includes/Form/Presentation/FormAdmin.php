<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Presentation;

use Contexis\Events\Shared\Presentation\Contracts\AdminService;

final class FormAdmin implements AdminService
{
	public function hook(): void
	{
		add_filter('views_edit-ctx-booking-form', [$this, 'injectFormTypeViews']);
		add_filter('views_edit-ctx-attendee-form', [$this, 'injectFormTypeViews']);
	}

	public function injectFormTypeViews(array $views): array
	{
		global $current_screen;
		
		$bookingUrl  = admin_url('edit.php?post_type=ctx-booking-form');
		$attendeeUrl = admin_url('edit.php?post_type=ctx-attendee-form');

		$isBooking  = ($current_screen->post_type === 'ctx-booking-form');
		$isAttendee = ($current_screen->post_type === 'ctx-attendee-form');

		$bookingLink  = sprintf(
			'<a href="%s" class="%s">%s</a>',
			$bookingUrl,
			$isBooking ? 'current' : '', // WP Standard-Klasse für aktiven Link
			__('Buchungsvorlagen', 'ctx-events')
		);

		$attendeeLink = sprintf(
			'<a href="%s" class="%s">%s</a>',
			$attendeeUrl,
			$isAttendee ? 'current' : '',
			__('Teilnehmervorlagen', 'ctx-events')
		);

		// Jetzt der Trick: Wir bauen ein neues Array, setzen unsere Links nach vorne
		// und fügen einen Trenner hinzu.
		$newViews = [
			'ctx-switch-booking'  => $bookingLink,
			'ctx-switch-attendee' => $attendeeLink,
		];

		// Die originalen Views (Alle, Trash, etc.) hängen wir hinten dran
		return array_merge($newViews, $views);
	}
}