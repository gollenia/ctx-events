<?php
declare(strict_types=1);

namespace Contexis\Events\Application\UseCases;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Payment\Domain\PaymentGateway;
class CreateBooking
{
    public function __construct(
        private BookingRepository $booking_repository,
        private EventRepository $event_repository,
        private PaymentGateway $payment_gateway,
        //private Mailer $mailer
    ) {
    }

    public function execute($data): void
    {
        // Validate and process the booking data
        $event = $this->event_repository->find($data['event_id']);

        if (!$event) {
            throw new \Exception("Event not found");
        }
        // Additional business logic can be added here (e.g., checking availability, applying discounts, etc.)
        $booking = $this->booking_repository->create($data);

        $payment_info = $this->payment_gateway->get_payment_info($data);

        // check if enough spaces are available
        $available_spaces = $this->booking_repository->get_spaces($event->id);




        //$this->mailer->sendConfirmation($data);
    }
}
