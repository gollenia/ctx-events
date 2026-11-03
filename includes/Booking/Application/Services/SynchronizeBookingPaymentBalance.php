<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Services;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\Enums\BookingLogEvent;
use Contexis\Events\Booking\Domain\Enums\BookingLogLevel;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Communication\Application\Contracts\BookingEmailTrigger;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

/** Keeps the outstanding payment balance aligned with an edited booking. */
final class SynchronizeBookingPaymentBalance
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private GatewayRepository $gatewayRepository,
        private BookingRepository $bookingRepository,
        private BookingEmailTrigger $bookingEmailTrigger,
        private Clock $clock,
        private CurrentActorProvider $currentActorProvider,
    ) {
    }

    public function execute(Booking $before, Booking $after): void
    {
        $beforeAmount = $before->priceSummary->finalPrice;
        $afterAmount = $after->priceSummary->finalPrice;

        if ($beforeAmount->equals($afterAmount) || $after->id === null) {
            return;
        }

        $transactions = $this->transactionRepository->findByBookingId($after->id)->toArray();
        $paidCents = 0;
        foreach ($transactions as $transaction) {
            if ($transaction->status === TransactionStatus::PAID) {
                $paidCents += $transaction->amount->amountCents;
            }
        }

        if ($afterAmount->amountCents > $beforeAmount->amountCents) {
            $this->createAdditionalPayment(
                $after,
                Price::from($afterAmount->amountCents - $beforeAmount->amountCents, $afterAmount->currency),
            );
            return;
        }

        // A lower total must never manufacture a negative transaction or refund.
        // Open payment requests are replaced with the actual outstanding balance.
        $this->cancelPendingTransactions($transactions);

        $outstandingCents = max(0, $afterAmount->amountCents - $paidCents);
        if ($outstandingCents > 0) {
            $this->createAdditionalPayment($after, Price::from($outstandingCents, $afterAmount->currency));
            return;
        }

        if ($paidCents > $afterAmount->amountCents) {
            $this->bookingRepository->update($after->appendLogEntry(new LogEntry(
                eventType: BookingLogEvent::Updated,
                level: BookingLogLevel::Warning,
                actor: $this->currentActorProvider->current(),
                timestamp: $this->clock->now(),
                message: sprintf(
                    'The booking total was reduced. Refund %.2f externally.',
                    ($paidCents - $afterAmount->amountCents) / 100,
                ),
            )));
        }
    }

    /** @param list<\Contexis\Events\Payment\Domain\Transaction> $transactions */
    private function cancelPendingTransactions(array $transactions): void
    {
        foreach ($transactions as $transaction) {
            if ($transaction->status !== TransactionStatus::PENDING) {
                continue;
            }

            $gateway = $this->gatewayRepository->find($transaction->gateway);
            if ($gateway === null) {
                throw new \DomainException("Payment gateway not found: {$transaction->gateway}");
            }

            $this->transactionRepository->save($gateway->cancelPayment($transaction));
        }
    }

    private function createAdditionalPayment(Booking $booking, Price $amount): void
    {
        if ($amount->isFree()) {
            return;
        }

        $gatewayId = trim((string) $booking->gateway);
        $gateway = $this->gatewayRepository->find($gatewayId);
        if ($gateway === null) {
            throw new \DomainException("Payment gateway not found: {$gatewayId}");
        }

        $transaction = $gateway->initiatePayment($booking, $amount);
        $this->transactionRepository->save($transaction);
        $this->bookingRepository->update($booking->appendLogEntry(new LogEntry(
            eventType: BookingLogEvent::Updated,
            level: BookingLogLevel::Info,
            actor: $this->currentActorProvider->current(),
            timestamp: $this->clock->now(),
            message: sprintf(
                'Open payment of %.2f created after booking update.',
                $amount->amountCents / 100,
            ),
        )));

        if ($transaction->checkoutUrl !== null) {
            $this->bookingEmailTrigger->trigger(EmailTrigger::BOOKING_PAYMENT_LINK, $transaction->bookingId);
        }
    }
}
