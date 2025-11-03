<?php

namespace Contexis\Events\Infrastructure\Wordpress;

use function DI\string;

final class Options {

	private array $settings = [
		'events_slug' => [
			'value' => 'events',
			'type' => 'string',
			'description' => 'The slug used for the main events page and event URLs.',
			'translatable' => true,
		],
		'currency' => [
			'value' => 'USD',
			'type' => 'string',
			'description' => 'The currency used for event bookings.',
			'translatable' => false,
		],
		'text_feedback_pending' => __('Booking successful, pending confirmation (you will also receive an email once confirmed).', 'events'),
		'text_feedback' => __('Booking successful.', 'events'),
		'text_feedback_full' => __('Booking cannot be made, not enough spaces available!', 'events'),
		'bookings_contact_email_pending_subject' => 'Booking Pending',
		'bookings_contact_email_pending_body' => '',
		'bookings_contact_email_confirmed_subject' => 'Booking Confirmed',
		'bookings_contact_email_confirmed_body' => '',
		'bookings_contact_email_rejected_subject' => 'Booking Rejected',
		'bookings_contact_email_rejected_body' => '',
		'bookings_contact_email_cancelled_subject' => 'Booking Cancelled',
		'bookings_contact_email_cancelled_body' => '',
		'bookings_email_pending_subject' => 'Booking Pending',
		'bookings_email_pending_body' => '',
		'bookings_email_rejected_subject' => 'Booking Rejected',
		'bookings_email_rejected_body' => '',
		'bookings_email_confirmed_subject' => 'Booking Confirmed',
		'bookings_email_confirmed_body' => '',
		'bookings_email_cancelled_subject' => 'Booking Cancelled',
		'bookings_email_cancelled_body' => '',
		'bookings_ical_attachments' => 1,
		
		
		'cron_emails_limit' => get_option('emp_cron_emails_limit', 100),
		'offline_booking_feedback' => __('Booking successful.', 'events')
	];

	public function register(): void {
		foreach ($this->settings as $key => $value) {
			if (get_option($key) === false) {
				add_option($key, $value);
			}
		}
	}


	
}