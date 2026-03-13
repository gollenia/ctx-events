<?php
declare(strict_types=1);

use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Event\Domain\Enums\BookingDenyReason;
use Contexis\Events\Event\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Payment\Domain\ValueObjects\BankData;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Domain\ValueObjects\PriceRange;

test('bank data normalizes iban and bic for valid values', function () {
    $bankData = BankData::fromValues(
        accountHolder: 'Max Mustermann',
        iban: 'de89 3704 0044 0532 0130 00',
        bic: 'cobadeffxxx',
        bankName: 'Musterbank'
    );

    expect($bankData)->not->toBeNull();
    expect($bankData->iban)->toBe('DE89370400440532013000');
    expect($bankData->bic)->toBe('COBADEFFXXX');
    expect($bankData->isValid())->toBeTrue();
});

test('bank data rejects invalid iban length', function () {
    expect(static fn () => BankData::fromValues('Max', 'DE12', 'COBADEFFXXX', 'Bank'))
        ->toThrow(InvalidArgumentException::class);
});

test('bank data rejects invalid iban checksum', function () {
    expect(static fn () => BankData::fromValues('Max', 'DE89370400440532013001', 'COBADEFFXXX', 'Bank'))
        ->toThrow(InvalidArgumentException::class, 'BANK_DATA_INVALID_IBAN_CHECKSUM');
});

test('bank data rejects invalid bic format', function () {
    expect(static fn () => BankData::fromValues('Max', 'DE89370400440532013000', 'bad-bic', 'Bank'))
        ->toThrow(InvalidArgumentException::class, 'BANK_DATA_INVALID_BIC');
});

test('booking policy denies when disabled', function () {
    $policy = BookingPolicy::createWithDisabledBookings();
    $decision = $policy->canBookAt(new DateTimeImmutable('2026-03-04 10:00:00'));

    expect($decision->allowed)->toBeFalse();
    expect($decision->reason)->toBe(BookingDenyReason::DISABLED);
});

test('booking policy denies before start and after end', function () {
    $policy = BookingPolicy::create(
        enabled: true,
        start: new DateTimeImmutable('2026-03-10 08:00:00'),
        end: new DateTimeImmutable('2026-03-20 20:00:00'),
        event_created_at: new DateTimeImmutable('2026-03-01 00:00:00'),
        event_start: new DateTimeImmutable('2026-03-25 00:00:00')
    );

    expect($policy->canBookAt(new DateTimeImmutable('2026-03-09 23:59:59'))->reason)->toBe(BookingDenyReason::NOT_STARTED);
    expect($policy->canBookAt(new DateTimeImmutable('2026-03-21 00:00:00'))->reason)->toBe(BookingDenyReason::ENDED);
    expect($policy->canBookAt(new DateTimeImmutable('2026-03-15 12:00:00'))->allowed)->toBeTrue();
});

test('booking policy throws on invalid booking window', function () {
    expect(static fn () => BookingPolicy::create(
        enabled: true,
        start: new DateTimeImmutable('2026-04-02 10:00:00'),
        end: new DateTimeImmutable('2026-04-01 10:00:00'),
        event_created_at: new DateTimeImmutable('2026-03-01 10:00:00'),
        event_start: new DateTimeImmutable('2026-04-15 10:00:00')
    ))->toThrow(DomainException::class);
});

test('price range rejects half-empty and mixed currencies', function () {
    $eur = Currency::fromCode('EUR');
    $usd = Currency::fromCode('USD');

    expect(static fn () => new PriceRange(new Price(100, $eur), null))->toThrow(DomainException::class);
    expect(static fn () => new PriceRange(new Price(100, $eur), new Price(200, $usd)))->toThrow(DomainException::class);
});

test('price range can be built from prices and empty state', function () {
    $eur = Currency::fromCode('EUR');
    $range = PriceRange::fromPrices(new Price(500, $eur), new Price(100, $eur), new Price(300, $eur));

    expect($range->min?->toInt())->toBe(100);
    expect($range->max?->toInt())->toBe(500);
    expect(PriceRange::fromPrices()->isEmpty())->toBeTrue();
});

test('booking status transition matrix is enforced', function () {
    expect(BookingStatus::PENDING->canTransitionTo(BookingStatus::APPROVED))->toBeTrue();
    expect(BookingStatus::PENDING->canTransitionTo(BookingStatus::DELETED))->toBeTrue();
    expect(BookingStatus::APPROVED->canTransitionTo(BookingStatus::EXPIRED))->toBeFalse();
    expect(BookingStatus::CANCELED->canTransitionTo(BookingStatus::DELETED))->toBeTrue();
    expect(BookingStatus::CANCELED->canTransitionTo(BookingStatus::PENDING))->toBeTrue();
    expect(BookingStatus::EXPIRED->canTransitionTo(BookingStatus::APPROVED))->toBeFalse();
    expect(BookingStatus::EXPIRED->canTransitionTo(BookingStatus::PENDING))->toBeTrue();
    expect(BookingStatus::DELETED->canTransitionTo(BookingStatus::PENDING))->toBeFalse();
});

test('price supports arithmetic, rounding and guards currency mismatch', function () {
    $eur = Currency::fromCode('EUR');
    $usd = Currency::fromCode('USD');

    $base = Price::fromFloat(10.005, $eur);
    expect($base->toInt())->toBe(1001);
    expect($base->percentageOf(10))->toBe(100);
    expect($base->subtract(new Price(2000, $eur))->toInt())->toBe(0);
    expect($base->add(new Price(99, $eur))->toInt())->toBe(1100);

    expect(static fn () => $base->add(new Price(1, $usd)))->toThrow(InvalidArgumentException::class);
});
