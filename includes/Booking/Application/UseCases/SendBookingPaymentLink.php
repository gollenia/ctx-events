<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\SendBookingPaymentLinkResponse;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Communication\Application\Contracts\BookingEmailTrigger;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;

final readonly class SendBookingPaymentLink
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private ResolveBookingPaymentLink $resolveBookingPaymentLink,
        private BookingEmailTrigger $bookingEmailTrigger,
    ) {
    }

    public function execute(string $reference): SendBookingPaymentLinkResponse
    {
        $booking = $this->bookingRepository->findByReference($reference);

        if ($booking === null) {
            throw new \DomainException("Booking with reference {$reference} not found");
        }

        if ($booking->status !== BookingStatus::PENDING) {
            throw new \DomainException('A payment link can only be sent for a pending booking.');
        }

        $transaction = $this->resolveBookingPaymentLink->execute($reference);

        return new SendBookingPaymentLinkResponse(
            transaction: $transaction,
            emailResult: $this->bookingEmailTrigger->trigger(
                EmailTrigger::BOOKING_PAYMENT_LINK,
                $transaction->bookingId,
            ),
        );
    }
}
