<?php

declare(strict_types=1);

use Contexis\Events\Booking\Application\Services\CancelBookingsForEvent;
use Contexis\Events\Booking\Application\Services\SyncOfflineTransactionForBookingAction;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Event\Application\UseCases\CancelEvent;
use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\EventStatusRepository;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeEventRepository;
use Tests\Support\FakeBookingEmailTrigger;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeCurrentActorProvider;
use Tests\Support\FakeTransactionRepository;

test('cancel event marks event as cancelled and cancels bookings with email option', function () {
    $event = FakeEventFactory::create(77);
    $eventRepository = FakeEventRepository::one($event);
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeEventBooking('BOOK-CANCEL-EVENT', $event->id, BookingStatus::APPROVED));
    $transactionRepository = FakeTransactionRepository::withTransactions(makeBookingTransaction($bookingId));
    $bookingEmailTrigger = new FakeBookingEmailTrigger();
    $eventStatusRepository = Mockery::mock(EventStatusRepository::class);
    $cancelBookingsForEvent = new CancelBookingsForEvent(
        bookingRepository: $bookingRepository,
        transactionSync: new SyncOfflineTransactionForBookingAction($transactionRepository),
        clock: makeEventBookingClock(),
        currentActorProvider: new FakeCurrentActorProvider(),
        bookingEmailTrigger: $bookingEmailTrigger,
    );

    $eventStatusRepository
        ->shouldReceive('saveStatus')
        ->once()
        ->with(Mockery::on(static function ($savedEvent) use ($event): bool {
            return $savedEvent->id->equals($event->id)
                && $savedEvent->status === EventStatus::Cancelled;
        }));

    $useCase = new CancelEvent(
        eventRepository: $eventRepository,
        eventStatusRepository: $eventStatusRepository,
        cancelBookingsForEvent: $cancelBookingsForEvent,
    );

    expect($useCase->execute(77, true, ''))->toBeTrue()
        ->and($bookingRepository->find($bookingId)?->status)->toBe(BookingStatus::CANCELED)
        ->and(count($bookingEmailTrigger->calls))->toBe(1)
        ->and($bookingEmailTrigger->calls[0]['cancellationReason'])->toBe('');
});

test('cancel event returns null without side effects when event does not exist', function () {
    $eventRepository = FakeEventRepository::empty();
    $bookingRepository = FakeBookingRepository::empty();
    $eventStatusRepository = Mockery::mock(EventStatusRepository::class);
    $cancelBookingsForEvent = new CancelBookingsForEvent(
        bookingRepository: $bookingRepository,
        transactionSync: new SyncOfflineTransactionForBookingAction(FakeTransactionRepository::empty()),
        clock: makeEventBookingClock(),
        currentActorProvider: new FakeCurrentActorProvider(),
        bookingEmailTrigger: new FakeBookingEmailTrigger(),
    );

    $eventStatusRepository->shouldNotReceive('saveStatus');

    $useCase = new CancelEvent(
        eventRepository: $eventRepository,
        eventStatusRepository: $eventStatusRepository,
        cancelBookingsForEvent: $cancelBookingsForEvent,
    );

    expect($useCase->execute(999, true, ''))->toBeFalse()
        ->and($bookingRepository->findByEventId(\Contexis\Events\Event\Domain\ValueObjects\EventId::from(999)))->toBe([]);
});
