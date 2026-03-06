<?php
declare(strict_types=1);

use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\BookingTokenRecord;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookings;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Event\Domain\ValueObjects\EventViewConfig;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Form\Domain\ValueObjects\VisibilityRule;

test('ticket bookings computes counts correctly', function () {
    $ticketId = TicketId::from('ticket-standard');
    if ($ticketId === null) {
        throw new RuntimeException('Failed to create ticket id in test.');
    }

    $ticketBookings = new TicketBookings(
        ticketId: $ticketId,
        pending: 2,
        approved: 5,
        canceled: 1,
        expired: 3
    );

    expect($ticketBookings->getBookedCount())->toBe(7);
    expect($ticketBookings->getLostCount())->toBe(4);
    expect($ticketBookings->getCountFor(BookingStatus::PENDING))->toBe(2);
    expect($ticketBookings->getCountFor(BookingStatus::APPROVED))->toBe(5);
});

test('ticket bookings map aggregates and falls back to empty stats', function () {
    $map = TicketBookingsMap::fromArray([
        'ticket-a' => ['pending' => 1, 'approved' => 2, 'canceled' => 0, 'expired' => 1],
        'ticket-b' => ['pending' => 3, 'approved' => 4, 'canceled' => 1, 'expired' => 0],
    ]);

    $ticketA = TicketId::from('ticket-a');
    $missing = TicketId::from('ticket-missing');

    if ($ticketA === null || $missing === null) {
        throw new RuntimeException('Failed to create ticket ids in test.');
    }

    expect($map->getCountFor($ticketA, BookingStatus::APPROVED))->toBe(2);
    expect($map->getTotalBookedCount())->toBe(10);
    expect($map->getTotalPendingCount())->toBe(4);
    expect($map->getTotalApprovedCount())->toBe(6);
    expect($map->getStatsFor($missing)->getBookedCount())->toBe(0);
});

test('event view config shows free spaces based on toggle and threshold', function () {
    $config = new EventViewConfig(showFreeSpacesThreshold: 5, showFreeSpaces: true);
    expect($config->showFreeSpaces(3))->toBeTrue();
    expect($config->showFreeSpaces(8))->toBeFalse();

    $hidden = new EventViewConfig(showFreeSpacesThreshold: 100, showFreeSpaces: false);
    expect($hidden->showFreeSpaces(1))->toBeFalse();
});

test('visibility rule evaluates operators correctly', function () {
    $equals = new VisibilityRule('audience', 'adult', 'equals');
    $notEquals = new VisibilityRule('audience', 'child', 'not_equals');
    $notEmpty = new VisibilityRule('code', null, 'not_empty');

    expect($equals->isMet(['audience' => 'adult']))->toBeTrue();
    expect($equals->isMet(['audience' => 'child']))->toBeFalse();
    expect($notEquals->isMet(['audience' => 'adult']))->toBeTrue();
    expect($notEmpty->isMet(['code' => 'ABC123']))->toBeTrue();
    expect($notEmpty->isMet(['code' => '0']))->toBeFalse();
});

test('booking token record validates fields and expiry checks', function () {
    $record = BookingTokenRecord::fromArray([
        'tokenId' => 'token-1',
        'eventId' => 44,
        'sessionHash' => 'hash-abc',
        'expiresAt' => '2026-03-04T12:00:00+00:00',
        'used' => true,
    ]);

    expect($record->toArray()['eventId'])->toBe(44);
    expect($record->isExpiredAt(new DateTimeImmutable('2026-03-04T12:00:00+00:00')))->toBeTrue();
    expect($record->isExpiredAt(new DateTimeImmutable('2026-03-04T11:59:59+00:00')))->toBeFalse();

    expect(static fn () => new BookingTokenRecord('', 1, 'hash', new DateTimeImmutable()))->toThrow(InvalidArgumentException::class);
    expect(static fn () => new BookingTokenRecord('token', 0, 'hash', new DateTimeImmutable()))->toThrow(InvalidArgumentException::class);
    expect(static fn () => new BookingTokenRecord('token', 1, '', new DateTimeImmutable()))->toThrow(InvalidArgumentException::class);
});
