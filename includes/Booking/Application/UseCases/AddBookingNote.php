<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\AddBookingNoteRequest;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNote;

final class AddBookingNote
{
    public function __construct(
        private BookingRepository $bookingRepository,
    ) {
    }

    public function execute(AddBookingNoteRequest $request): BookingNote
    {
        $booking = $this->bookingRepository->findByReference($request->uuid);

        if ($booking === null) {
            throw new \DomainException('Booking not found.');
        }

        $text = trim($request->text);

        if ($text == '') {
            throw new \DomainException('Note text is required.');
        }

        $note = BookingNote::create($text, $request->author);
        $id = $booking->id ?? throw new \RuntimeException('Booking has no ID');
        $notes = $booking->notes->add($note);

        $this->bookingRepository->updateNotes($id, $notes);

        return $note;
    }
}
