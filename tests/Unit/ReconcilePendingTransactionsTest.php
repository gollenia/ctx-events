<?php
declare(strict_types=1);

use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Payment\Application\Services\SyncBookingFromTransaction;
use Contexis\Events\Payment\Application\UseCases\ReconcilePendingTransactions;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeGatewayRepository;
use Tests\Support\FakePaymentGateway;
use Tests\Support\FakeReconcilableTransactionFinder;
use Tests\Support\FakeTransactionRepository;

function makeReconcileClock(): Clock
{
    $clock = Mockery::mock(Clock::class);
    $clock->allows('now')->andReturn(new DateTimeImmutable('2026-03-16 12:00:00'));

    return $clock;
}

test('reconciliation expires offline transactions that passed their deadline and expires the booking', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeOfflineBooking());
    $transaction = Transaction::forBankTransfer(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        gateway: 'offline',
        expiresAt: new DateTimeImmutable('2026-03-15 11:00:00'),
    );
    $transactionRepository = FakeTransactionRepository::withTransactions($transaction);

    $useCase = new ReconcilePendingTransactions(
        findReconcilableTransactions: new FakeReconcilableTransactionFinder([$transaction]),
        transactionRepository: $transactionRepository,
        gatewayRepository: FakeGatewayRepository::empty(),
        syncBookingFromTransaction: new SyncBookingFromTransaction($bookingRepository, makeReconcileClock()),
        clock: makeReconcileClock(),
    );

    expect($useCase->execute())->toBe(1)
        ->and($transactionRepository->findLatestByBookingId($bookingId)?->status)->toBe(TransactionStatus::EXPIRED)
        ->and($bookingRepository->find($bookingId)?->status)->toBe(BookingStatus::EXPIRED);
});

test('reconciliation verifies stale online transactions and expires the booking when the gateway reports expired', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeOnlineSyncBooking());
    $transaction = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_reconcile_1',
        checkoutUrl: \Uri\Rfc3986\Uri::parse('https://checkout.example.test/pay'),
        gateway: 'mollie',
        gatewayUrl: \Uri\Rfc3986\Uri::parse('https://gateway.example.test/pay/tr_reconcile_1'),
    );
    $transaction = $transaction->withCreatedAt(new DateTimeImmutable('2026-03-16 11:30:00'));
    $transactionRepository = FakeTransactionRepository::withTransactions($transaction);

    $gateway = new FakePaymentGateway(
        id: 'mollie',
        verifyPaymentUsing: static fn(Transaction $transaction): Transaction => $transaction->expire(),
    );

    $useCase = new ReconcilePendingTransactions(
        findReconcilableTransactions: new FakeReconcilableTransactionFinder([$transaction]),
        transactionRepository: $transactionRepository,
        gatewayRepository: FakeGatewayRepository::withGateways([$gateway]),
        syncBookingFromTransaction: new SyncBookingFromTransaction($bookingRepository, makeReconcileClock()),
        clock: makeReconcileClock(),
    );

    expect($useCase->execute())->toBe(1)
        ->and($transactionRepository->findByExternalId('tr_reconcile_1')?->status)->toBe(TransactionStatus::EXPIRED)
        ->and($bookingRepository->find($bookingId)?->status)->toBe(BookingStatus::EXPIRED);
});
