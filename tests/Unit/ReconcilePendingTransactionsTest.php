<?php
declare(strict_types=1);

use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Application\Contracts\BookingOptions;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Infrastructure\EventMeta;
use Contexis\Events\Payment\Application\Services\SyncBookingFromTransaction;
use Contexis\Events\Payment\Application\UseCases\ReconcilePendingTransactions;
use Contexis\Events\Payment\Infrastructure\Wordpress\ReconcilePendingTransactionsCron;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Tests\Support\FakeBookingEmailTrigger;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeBookingOptions;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeEventRepository;
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

function makeReconcileOnlineBooking(): Booking
{
    return new Booking(
        reference: BookingReference::fromString('BOOK-RECON-ONLINE'),
        email: new Email('online@example.test'),
        name: PersonName::from('Erika', 'Muster'),
        priceSummary: PriceSummary::fromValues(
            bookingPrice: Price::from(5000, Currency::fromCode('EUR')),
            donationAmount: Price::from(0, Currency::fromCode('EUR')),
            discountAmount: Price::from(0, Currency::fromCode('EUR'))
        ),
        bookingTime: new DateTimeImmutable('2026-03-16 10:00:00'),
        status: BookingStatus::PENDING,
        registration: new RegistrationData([
            'email' => 'online@example.test',
            'first_name' => 'Erika',
            'last_name' => 'Muster',
        ]),
        attendees: AttendeeCollection::empty(),
        gateway: 'mollie',
        coupon: null,
        transactions: null,
        eventId: EventId::from(2),
    );
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
    $bookingEmailTrigger = new FakeBookingEmailTrigger();

    $useCase = new ReconcilePendingTransactions(
        findReconcilableTransactions: new FakeReconcilableTransactionFinder([$transaction]),
        transactionRepository: $transactionRepository,
        gatewayRepository: FakeGatewayRepository::empty(),
        syncBookingFromTransaction: new SyncBookingFromTransaction(
            $bookingRepository,
            FakeEventRepository::one(FakeEventFactory::create(1)),
            $transactionRepository,
            makeReconcileClock(),
            $bookingEmailTrigger,
        ),
        clock: makeReconcileClock(),
    );

    expect($useCase->execute())->toBe(1)
        ->and($transactionRepository->findLatestByBookingId($bookingId)?->status)->toBe(TransactionStatus::EXPIRED)
        ->and($bookingRepository->find($bookingId)?->status)->toBe(BookingStatus::EXPIRED)
        ->and($bookingEmailTrigger->lastCall()['trigger'] ?? null)->toBe(EmailTrigger::BOOKING_OFFLINE_EXPIRED);
});

test('reconciliation expires online transactions via expiresAt and expires the booking', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeOnlineSyncBooking());
    $transaction = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_reconcile_1',
        checkoutUrl: \Uri\Rfc3986\Uri::parse('https://checkout.example.test/pay'),
        gateway: 'mollie',
        gatewayUrl: \Uri\Rfc3986\Uri::parse('https://gateway.example.test/pay/tr_reconcile_1'),
        expiresAt: new DateTimeImmutable('2026-03-16 11:45:00'),
    );
    $transactionRepository = FakeTransactionRepository::withTransactions($transaction);
    $bookingEmailTrigger = new FakeBookingEmailTrigger();

    $gateway = new FakePaymentGateway(
        id: 'mollie',
        verifyPaymentUsing: static fn(Transaction $transaction): Transaction => $transaction->expire(),
    );

    $useCase = new ReconcilePendingTransactions(
        findReconcilableTransactions: new FakeReconcilableTransactionFinder([$transaction]),
        transactionRepository: $transactionRepository,
        gatewayRepository: FakeGatewayRepository::withGateways([$gateway]),
        syncBookingFromTransaction: new SyncBookingFromTransaction(
            $bookingRepository,
            FakeEventRepository::one(FakeEventFactory::create(2)),
            $transactionRepository,
            makeReconcileClock(),
            $bookingEmailTrigger,
        ),
        clock: makeReconcileClock(),
    );

    expect($useCase->execute())->toBe(1)
        ->and($transactionRepository->findByExternalId('tr_reconcile_1')?->status)->toBe(TransactionStatus::EXPIRED)
        ->and($bookingRepository->find($bookingId)?->status)->toBe(BookingStatus::EXPIRED);
});

test('reconciliation does not expire a booking when a newer pending transaction exists', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeReconcileOnlineBooking());

    $expiredCandidate = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_old_expiring',
        checkoutUrl: \Uri\Rfc3986\Uri::parse('https://checkout.example.test/pay/old'),
        gateway: 'mollie',
        gatewayUrl: \Uri\Rfc3986\Uri::parse('https://gateway.example.test/pay/tr_old_expiring'),
    )->withCreatedAt(new DateTimeImmutable('2026-03-16 11:00:00'))
        ->withExpiresAt(new DateTimeImmutable('2026-03-16 11:30:00'));

    $replacement = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_new_pending',
        checkoutUrl: \Uri\Rfc3986\Uri::parse('https://checkout.example.test/pay/new'),
        gateway: 'mollie',
        gatewayUrl: \Uri\Rfc3986\Uri::parse('https://gateway.example.test/pay/tr_new_pending'),
    )->withCreatedAt(new DateTimeImmutable('2026-03-16 11:45:00'))
        ->withExpiresAt(new DateTimeImmutable('2026-03-16 12:30:00'));

    $transactionRepository = FakeTransactionRepository::withTransactions($expiredCandidate, $replacement);
    $bookingEmailTrigger = new FakeBookingEmailTrigger();

    $gateway = new FakePaymentGateway(
        id: 'mollie',
        verifyPaymentUsing: static fn(Transaction $transaction): Transaction => $transaction->expire(),
    );

    $useCase = new ReconcilePendingTransactions(
        findReconcilableTransactions: new FakeReconcilableTransactionFinder([$expiredCandidate]),
        transactionRepository: $transactionRepository,
        gatewayRepository: FakeGatewayRepository::withGateways([$gateway]),
        syncBookingFromTransaction: new SyncBookingFromTransaction(
            $bookingRepository,
            FakeEventRepository::one(FakeEventFactory::create(2)),
            $transactionRepository,
            makeReconcileClock(),
            $bookingEmailTrigger,
        ),
        clock: makeReconcileClock(),
    );

    expect($useCase->execute())->toBe(1)
        ->and($transactionRepository->findByExternalId('tr_old_expiring')?->status)->toBe(TransactionStatus::EXPIRED)
        ->and($bookingRepository->find($bookingId)?->status)->toBe(BookingStatus::PENDING)
        ->and($transactionRepository->findByExternalId('tr_new_pending')?->status)->toBe(TransactionStatus::PENDING)
        ->and($bookingEmailTrigger->calls)->toBe([]);
});

test('reconciliation leaves past-event bookings untouched even when the transaction expires', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeReconcileOnlineBooking());

    $transaction = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_past_event_expired',
        checkoutUrl: \Uri\Rfc3986\Uri::parse('https://checkout.example.test/pay/past'),
        gateway: 'mollie',
        gatewayUrl: \Uri\Rfc3986\Uri::parse('https://gateway.example.test/pay/tr_past_event_expired'),
        expiresAt: new DateTimeImmutable('2026-03-16 11:00:00'),
    );
    $transactionRepository = FakeTransactionRepository::withTransactions($transaction);
    $bookingEmailTrigger = new FakeBookingEmailTrigger();

    $pastEvent = FakeEventFactory::create(2, [
        EventMeta::EVENT_START => '2026-03-01 10:00:00',
        EventMeta::EVENT_END => '2026-03-02 10:00:00',
    ]);

    $useCase = new ReconcilePendingTransactions(
        findReconcilableTransactions: new FakeReconcilableTransactionFinder([$transaction]),
        transactionRepository: $transactionRepository,
        gatewayRepository: FakeGatewayRepository::empty(),
        syncBookingFromTransaction: new SyncBookingFromTransaction(
            $bookingRepository,
            FakeEventRepository::one($pastEvent),
            $transactionRepository,
            makeReconcileClock(),
            $bookingEmailTrigger,
        ),
        clock: makeReconcileClock(),
    );

    expect($useCase->execute())->toBe(1)
        ->and($transactionRepository->findByExternalId('tr_past_event_expired')?->status)->toBe(TransactionStatus::EXPIRED)
        ->and($bookingRepository->find($bookingId)?->status)->toBe(BookingStatus::PENDING)
        ->and($bookingEmailTrigger->calls)->toBe([]);
});

test('cron does not reconcile expired bookings automatically when the setting is disabled', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeOfflineBooking());
    $transaction = Transaction::forBankTransfer(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        gateway: 'offline',
        expiresAt: new DateTimeImmutable('2026-03-15 11:00:00'),
    );
    $transactionRepository = FakeTransactionRepository::withTransactions($transaction);
    $bookingEmailTrigger = new FakeBookingEmailTrigger();

    $cron = new ReconcilePendingTransactionsCron(
        reconcilePendingTransactions: new ReconcilePendingTransactions(
            findReconcilableTransactions: new FakeReconcilableTransactionFinder([$transaction]),
            transactionRepository: $transactionRepository,
            gatewayRepository: FakeGatewayRepository::empty(),
            syncBookingFromTransaction: new SyncBookingFromTransaction(
                $bookingRepository,
                FakeEventRepository::one(FakeEventFactory::create(1)),
                $transactionRepository,
                makeReconcileClock(),
                $bookingEmailTrigger,
            ),
            clock: makeReconcileClock(),
        ),
        bookingOptions: new FakeBookingOptions(denyExpiredBookings: false),
    );

    $cron->run();

    expect($transactionRepository->findLatestByBookingId($bookingId)?->status)->toBe(TransactionStatus::PENDING)
        ->and($bookingRepository->find($bookingId)?->status)->toBe(BookingStatus::PENDING);
});

test('wordpress cron does not run when external reconciliation mode is enabled', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeOfflineBooking());
    $transaction = Transaction::forBankTransfer(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        gateway: 'offline',
        expiresAt: new DateTimeImmutable('2026-03-15 11:00:00'),
    );
    $transactionRepository = FakeTransactionRepository::withTransactions($transaction);
    $bookingEmailTrigger = new FakeBookingEmailTrigger();

    $cron = new ReconcilePendingTransactionsCron(
        reconcilePendingTransactions: new ReconcilePendingTransactions(
            findReconcilableTransactions: new FakeReconcilableTransactionFinder([$transaction]),
            transactionRepository: $transactionRepository,
            gatewayRepository: FakeGatewayRepository::empty(),
            syncBookingFromTransaction: new SyncBookingFromTransaction(
                $bookingRepository,
                FakeEventRepository::one(FakeEventFactory::create(1)),
                $transactionRepository,
                makeReconcileClock(),
                $bookingEmailTrigger,
            ),
            clock: makeReconcileClock(),
        ),
        bookingOptions: new FakeBookingOptions(
            expirationSyncMode: BookingOptions::EXPIRATION_SYNC_MODE_EXTERNAL
        ),
    );

    $cron->run();

    expect($transactionRepository->findLatestByBookingId($bookingId)?->status)->toBe(TransactionStatus::PENDING)
        ->and($bookingRepository->find($bookingId)?->status)->toBe(BookingStatus::PENDING);
});
