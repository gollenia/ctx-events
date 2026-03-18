<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\BookingExportData;
use Contexis\Events\Booking\Application\DTOs\BookingExportSheet;
use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\FormRepository;

final class ExportEventBookings
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private EventRepository $eventRepository,
        private FormRepository $formRepository,
    ) {
    }

    public function execute(EventId $eventId, bool $includeAttendees): BookingExportData
    {
        $event = $this->eventRepository->find($eventId);

        if ($event === null) {
            throw new \DomainException(sprintf('Event with ID %d not found.', $eventId->toInt()));
        }

        $bookings = $this->bookingRepository->findByEventId($eventId);
        $ticketNames = $this->resolveTicketNames($event);
        $bookingFieldLabels = $this->resolveFormFieldLabels($event->forms?->bookingForm);
        $attendeeFieldLabels = $this->resolveFormFieldLabels($event->forms?->attendeeForm);

        $sheets = $includeAttendees
            ? [$this->buildAttendeesSheet($event, $bookings, $ticketNames, $bookingFieldLabels, $attendeeFieldLabels)]
            : [$this->buildBookingsSheet($event, $bookings, $bookingFieldLabels)];

        return new BookingExportData(
            fileName: sprintf('bookings-%s-%d', $this->slugify($event->name), $event->id->toInt()),
            sheets: $sheets,
        );
    }

    /**
     * @param Booking[] $bookings
     * @param array<string, string> $fieldLabels
     */
    private function buildBookingsSheet(Event $event, array $bookings, array $fieldLabels): BookingExportSheet
    {
        $metadataKeys = $this->collectMetadataKeys(
            array_map(
                static fn (Booking $booking): array => $booking->registration->all(),
                $bookings,
            ),
            ['booking_form_id', 'email', 'first_name', 'last_name'],
            $fieldLabels,
        );

        $headers = [
            'Buchungsreferenz',
            'Event-ID',
            'Event',
            'Buchungsdatum',
            'Status',
            'Vorname',
            'Nachname',
            'E-Mail',
            'Teilnehmer gesamt',
            'Gateway',
            'Buchungspreis',
            'Spende',
            'Rabatt',
            'Endpreis',
            'Waehrung',
            ...array_map(fn (string $key): string => $fieldLabels[$key] ?? $this->humanizeKey($key), $metadataKeys),
        ];

        $rows = [$headers];

        foreach ($bookings as $booking) {
            $registration = $booking->registration->all();
            $rows[] = [
                $booking->reference->toString(),
                $event->id->toInt(),
                $event->name,
                $booking->bookingTime->format('Y-m-d H:i:s'),
                $this->formatStatus($booking->status),
                $booking->name->firstName,
                $booking->name->lastName,
                $booking->email->toString(),
                $booking->countAttendees(),
                $booking->gateway ?? '',
                $this->formatMoney($booking->priceSummary->bookingPrice->toInt()),
                $this->formatMoney($booking->priceSummary->donationAmount->toInt()),
                $this->formatMoney($booking->priceSummary->discountAmount->toInt()),
                $this->formatMoney($booking->priceSummary->finalPrice->toInt()),
                $booking->priceSummary->finalPrice->currency->toString(),
                ...array_map(
                    fn (string $key): string|int|float|null => $this->normalizeCellValue($registration[$key] ?? null),
                    $metadataKeys,
                ),
            ];
        }

        return new BookingExportSheet('Buchungen', $rows);
    }

    /**
     * @param Booking[] $bookings
     * @param array<string, string> $ticketNames
     * @param array<string, string> $bookingFieldLabels
     * @param array<string, string> $attendeeFieldLabels
     */
    private function buildAttendeesSheet(
        Event $event,
        array $bookings,
        array $ticketNames,
        array $bookingFieldLabels,
        array $attendeeFieldLabels,
    ): BookingExportSheet {
        $bookingMetadataKeys = $this->collectMetadataKeys(
            array_map(
                static fn (Booking $booking): array => $booking->registration->all(),
                $bookings,
            ),
            ['booking_form_id', 'email', 'first_name', 'last_name'],
            $bookingFieldLabels,
        );

        $attendeeMetadataKeys = $this->collectMetadataKeys(
            $this->collectAttendeeMetadata($bookings),
            ['attendee_form_id'],
            $attendeeFieldLabels,
        );

        $headers = [
            'Buchungsreferenz',
            'Event-ID',
            'Event',
            'Buchungsdatum',
            'Buchungsstatus',
            'Bucher Vorname',
            'Bucher Nachname',
            'Bucher E-Mail',
            'Gateway',
            'Buchungspreis',
            'Spende',
            'Rabatt',
            'Endpreis',
            'Ticket-ID',
            'Ticket',
            'Teilnehmer Vorname',
            'Teilnehmer Nachname',
            'Ticketpreis',
            'Waehrung',
            ...array_map(
                fn (string $key): string => $bookingFieldLabels[$key] ?? $this->humanizeKey($key),
                $bookingMetadataKeys,
            ),
            ...array_map(
                fn (string $key): string => $attendeeFieldLabels[$key] ?? $this->humanizeKey($key),
                $attendeeMetadataKeys,
            ),
        ];

        $rows = [$headers];

        foreach ($bookings as $booking) {
            foreach ($booking->attendees as $attendee) {
                $rows[] = $this->buildAttendeeRow(
                    $event,
                    $booking,
                    $attendee,
                    $ticketNames,
                    $bookingMetadataKeys,
                    $attendeeMetadataKeys,
                );
            }
        }

        return new BookingExportSheet('Teilnehmer', $rows);
    }

    /**
     * @param array<string, string> $ticketNames
     * @param string[] $bookingMetadataKeys
     * @param string[] $attendeeMetadataKeys
     * @return array<int, string|int|float|null>
     */
    private function buildAttendeeRow(
        Event $event,
        Booking $booking,
        Attendee $attendee,
        array $ticketNames,
        array $bookingMetadataKeys,
        array $attendeeMetadataKeys,
    ): array {
        $registration = $booking->registration->all();

        return [
            $booking->reference->toString(),
            $event->id->toInt(),
            $event->name,
            $booking->bookingTime->format('Y-m-d H:i:s'),
            $this->formatStatus($booking->status),
            $booking->name->firstName,
            $booking->name->lastName,
            $booking->email->toString(),
            $booking->gateway ?? '',
            $this->formatMoney($booking->priceSummary->bookingPrice->toInt()),
            $this->formatMoney($booking->priceSummary->donationAmount->toInt()),
            $this->formatMoney($booking->priceSummary->discountAmount->toInt()),
            $this->formatMoney($booking->priceSummary->finalPrice->toInt()),
            $attendee->ticketId->toString(),
            $ticketNames[$attendee->ticketId->toString()] ?? $attendee->ticketId->toString(),
            $attendee->name !== null ? $attendee->name->firstName : '',
            $attendee->name !== null ? $attendee->name->lastName : '',
            $this->formatMoney($attendee->ticketPrice->toInt()),
            $attendee->ticketPrice->currency->toString(),
            ...array_map(
                fn (string $key): string|int|float|null => $this->normalizeCellValue($registration[$key] ?? null),
                $bookingMetadataKeys,
            ),
            ...array_map(
                fn (string $key): string|int|float|null => $this->normalizeCellValue($attendee->metadata[$key] ?? null),
                $attendeeMetadataKeys,
            ),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     * @param string[] $skipKeys
     * @param array<string, string> $preferredLabels
     * @return string[]
     */
    private function collectMetadataKeys(array $entries, array $skipKeys, array $preferredLabels): array
    {
        $keys = [];

        foreach ($entries as $entry) {
            foreach ($entry as $key => $value) {
                if (in_array($key, $skipKeys, true) || $value === null || $value === '') {
                    continue;
                }

                $keys[$key] = true;
            }
        }

        $orderedKeys = [];
        foreach (array_keys($preferredLabels) as $key) {
            if (isset($keys[$key])) {
                $orderedKeys[] = $key;
                unset($keys[$key]);
            }
        }

        $remaining = array_keys($keys);
        sort($remaining, SORT_NATURAL | SORT_FLAG_CASE);

        return [...$orderedKeys, ...$remaining];
    }

    /**
     * @param Booking[] $bookings
     * @return array<int, array<string, mixed>>
     */
    private function collectAttendeeMetadata(array $bookings): array
    {
        $metadata = [];

        foreach ($bookings as $booking) {
            foreach ($booking->attendees as $attendee) {
                $metadata[] = $attendee->metadata;
            }
        }

        return $metadata;
    }

    /**
     * @return array<string, string>
     */
    private function resolveTicketNames(Event $event): array
    {
        $ticketNames = [];

        foreach ($event->tickets?->toArray() ?? [] as $ticket) {
            $ticketNames[$ticket->id->toString()] = $ticket->name;
        }

        return $ticketNames;
    }

    /**
     * @return array<string, string>
     */
    private function resolveFormFieldLabels(?FormId $formId): array
    {
        if ($formId === null) {
            return [];
        }

        $form = $this->formRepository->find($formId);
        if ($form === null) {
            return [];
        }

        $labels = [];

        foreach ($form->fields as $field) {
            $labels[$field->name] = $field->label;
        }

        return $labels;
    }

    private function normalizeCellValue(mixed $value): string|int|float|null
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $encoded === false ? '' : $encoded;
        }

        return (string) $value;
    }

    private function formatStatus(BookingStatus $status): string
    {
        return match ($status) {
            BookingStatus::PENDING => 'Pending',
            BookingStatus::APPROVED => 'Approved',
            BookingStatus::CANCELED => 'Canceled',
            BookingStatus::EXPIRED => 'Expired',
        };
    }

    private function formatMoney(int $amountCents): string
    {
        return number_format($amountCents / 100, 2, '.', '');
    }

    private function humanizeKey(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }

    private function slugify(string $value): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $value), '-'));

        return $slug !== '' ? $slug : 'event';
    }
}
