<?php
declare(strict_types=1);

use Contexis\Events\Booking\Application\UseCases\ExportEventBookings;
use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Infrastructure\EventMeta;
use Contexis\Events\Form\Domain\AttendeeForm;
use Contexis\Events\Form\Domain\BookingForm;
use Contexis\Events\Form\Domain\Enums\FormType;
use Contexis\Events\Form\Domain\Enums\InputType;
use Contexis\Events\Form\Domain\Fields\FormField;
use Contexis\Events\Form\Domain\Fields\FormFieldCollection;
use Contexis\Events\Form\Domain\Fields\InputDetails;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeEventRepository;
use Tests\Support\FakeFormRepository;

test('exports bookings with optional attendee sheet', function () {
    $event = FakeEventFactory::create(101, [
        EventMeta::BOOKING_FORM => 11,
        EventMeta::ATTENDEE_FORM => 12,
        EventMeta::TICKETS => [[
            'ticket_id' => 'vip-1',
            'ticket_name' => 'VIP',
            'ticket_description' => 'VIP ticket',
            'ticket_price' => 2500,
            'ticket_spaces' => 25,
            'ticket_max' => 4,
            'ticket_min' => 1,
            'ticket_enabled' => true,
            'ticket_start' => '2026-03-01 10:00:00',
            'ticket_end' => '2026-04-01 10:00:00',
            'ticket_order' => 1,
            'ticket_form' => 12,
        ]],
    ]);

    $forms = $event->forms ?? throw new RuntimeException('Missing forms');
    $bookingFormId = $forms->bookingForm;
    $attendeeFormId = $forms->attendeeForm;
    $currency = $event->currency ?? throw new RuntimeException('Missing currency');
    $ticket = $event->tickets?->toArray()[0] ?? throw new RuntimeException('Missing ticket');

    $formRepository = new FakeFormRepository(
        new BookingForm(
            id: $bookingFormId,
            type: FormType::BOOKING,
            fields: FormFieldCollection::from(
                new FormField('company', 'Firma', false, new InputDetails(InputType::TEXT)),
            ),
            name: 'Booking Export Form',
            description: null,
        ),
        new AttendeeForm(
            id: $attendeeFormId,
            type: FormType::ATTENDEE,
            fields: FormFieldCollection::from(
                new FormField('department', 'Abteilung', false, new InputDetails(InputType::TEXT)),
            ),
            name: 'Attendee Export Form',
            description: null,
        ),
    );

    $bookingRepository = FakeBookingRepository::empty();
    $bookingRepository->save(new Booking(
        reference: BookingReference::fromString('BOOK-101'),
        email: new Email('booking@example.test'),
        name: PersonName::from('Max', 'Muster'),
        priceSummary: PriceSummary::fromValues(
            bookingPrice: Price::from(2500, $currency),
            donationAmount: Price::from(500, $currency),
            discountAmount: Price::from(0, $currency),
        ),
        bookingTime: new DateTimeImmutable('2026-03-12 09:30:00'),
        status: BookingStatus::APPROVED,
        registration: new RegistrationData([
            'email' => 'booking@example.test',
            'first_name' => 'Max',
            'last_name' => 'Muster',
            'company' => 'Acme GmbH',
            'booking_form_id' => 11,
        ]),
        attendees: AttendeeCollection::from(
            new Attendee(
                ticketId: $ticket->id,
                ticketPrice: Price::from(2500, $currency),
                name: PersonName::from('Erika', 'Gast'),
                birthDate: null,
                metadata: [
                    'department' => 'Vertrieb',
                    'attendee_form_id' => 12,
                ],
            ),
        ),
        gateway: 'manual',
        coupon: null,
        transactions: null,
        eventId: $event->id,
    ));

    $useCase = new ExportEventBookings(
        bookingRepository: $bookingRepository,
        eventRepository: FakeEventRepository::one($event),
        formRepository: $formRepository,
    );

    $bookingExport = $useCase->execute($event->id, false);
    $export = $useCase->execute($event->id, true);

    expect($bookingExport->sheets)->toHaveCount(1);
    expect($bookingExport->sheets[0]->name)->toBe('Buchungen');
    expect($export->sheets)->toHaveCount(1);
    expect($export->sheets[0]->name)->toBe('Teilnehmer');
    expect($export->sheets[0]->rows[0])->toContain('Firma');
    expect($export->sheets[0]->rows[0])->toContain('Abteilung');
    expect($export->sheets[0]->rows[1])->toContain('Acme GmbH');
    expect($export->sheets[0]->rows[1])->toContain('VIP');
    expect($export->sheets[0]->rows[1])->toContain('Vertrieb');
    expect($export->sheets[0]->rows[1])->toContain('BOOK-101');
});
