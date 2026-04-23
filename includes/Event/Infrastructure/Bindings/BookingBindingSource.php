<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure\Bindings;

use Contexis\Events\Event\Application\DTOs\EventBookingSummary;
use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use WP_Block;

final class BookingBindingSource
{
	public const SOURCE_NAME = 'ctx-events/booking';

	public function register(): void
	{
		if (did_action('init')) {
			$this->registerBindingSource();
			return;
		}

		add_action('init', [$this, 'registerBindingSource']);
	}

	public function registerBindingSource(): void
	{
		if (!function_exists('register_block_bindings_source')) {
			return;
		}

		register_block_bindings_source(
			self::SOURCE_NAME,
			[
				'label' => __('Booking', 'ctx-events'),
				'uses_context' => ['ctx-events/eventId', 'postId', 'postType'],
				'get_value_callback' => [$this, 'getValue'],
			]
		);
	}

	/**
	 * @param array<string, mixed> $source_args
	 */
	public function getValue(array $source_args, WP_Block $block_instance, string $attribute_name): mixed
	{
		$field = isset($source_args['field']) && is_string($source_args['field'])
			? $source_args['field']
			: '';

		if ($field === '') {
			return '';
		}

		$summary = EventBindingContext::getEventResponse($block_instance)?->bookingSummary;
		if (!$summary instanceof EventBookingSummary) {
			return '';
		}

		return match ($field) {
			'statusLabel' => $this->getStatusLabel($summary),
			'priceLabel' => $this->getPriceLabel($summary),
			'availableSpacesLabel' => $this->getAvailableSpacesLabel($summary),
			'availableSpaces' => $summary->available ?? '',
			'approvedBookings' => $summary->approved,
			'pendingBookings' => $summary->pending ?? '',
			'totalCapacity' => $summary->totalCapacity ?? '',
			'bookingStartLabel' => $this->getDateTimeLabel($summary->bookingStart),
			'bookingEndLabel' => $this->getDateTimeLabel($summary->bookingEnd),
			default => '',
		};
	}

	private function getStatusLabel(EventBookingSummary $summary): string
	{
		if ($summary->isBookable) {
			return __('Bookable', 'ctx-events');
		}

		return match ($summary->denyReason?->value) {
			'disabled' => __('Booking disabled', 'ctx-events'),
			'no_capacity', 'sold_out' => __('Sold out', 'ctx-events'),
			'not_started' => __('Booking not started yet', 'ctx-events'),
			'ended' => __('Booking ended', 'ctx-events'),
			'form_error' => __('Booking form missing', 'ctx-events'),
			'no_tickets' => __('No tickets available', 'ctx-events'),
			default => __('Not bookable', 'ctx-events'),
		};
	}

	private function getPriceLabel(EventBookingSummary $summary): string
	{
		if ($summary->lowestPrice === null) {
			return '';
		}

		$lowestPrice = $summary->lowestPrice;
		$highestPrice = $summary->highestPrice ?? $lowestPrice;

		if ($lowestPrice->isFree() && $highestPrice->isFree()) {
			return __('Free', 'ctx-events');
		}

		return $lowestPrice->equals($highestPrice)
			? BlockEventLoader::formatPrice($lowestPrice)
			: BlockEventLoader::formatPrice($lowestPrice) . ' – ' . BlockEventLoader::formatPrice($highestPrice);
	}

	private function getAvailableSpacesLabel(EventBookingSummary $summary): string
	{
		if ($summary->available === null) {
			return '';
		}

		if ($summary->available === 0) {
			return __('No spaces left', 'ctx-events');
		}

		return sprintf(_n('%s space left', '%s spaces left', $summary->available, 'ctx-events'), $summary->available);
	}

	private function getDateTimeLabel(?\DateTimeImmutable $date): string
	{
		if (!$date instanceof \DateTimeImmutable) {
			return '';
		}

		return wp_date(
			sprintf('%s %s', get_option('date_format'), get_option('time_format')),
			$date->getTimestamp()
		);
	}
}
