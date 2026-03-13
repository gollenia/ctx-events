<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\Services;

use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Payment\Domain\Coupon;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class CalculateBookingPrice
{

	public function perform(
		TicketCollection $availableTickets,
	    ?Coupon $coupon,
		AttendeeCollection $attendees,
		?Price $donation,
		Currency $currency
	): PriceSummary
	{
		$bookingPrice = new Price(0, $currency);
		foreach ($attendees as $attendee) {
			$ticket = $availableTickets->getTicketById($attendee->ticketId);
			if ($ticket === null) {
				throw new \DomainException('Invalid ticket ID: ' . $attendee->ticketId->toString());
			}

			$bookingPrice = $bookingPrice->add($ticket->price);
		}

		if($bookingPrice->isFree()) return PriceSummary::free($currency);

		$discount = $coupon?->getDiscountAmount($bookingPrice) ?? new Price(0, $currency);
		
		return PriceSummary::fromValues(
			bookingPrice: $bookingPrice,
			donationAmount: $donation ?? new Price(0, $currency),
			discountAmount: $discount
		);
	}
}
