<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\UseCases;

use Contexis\Events\Booking\Application\UseCases\ResolveBookingPaymentLink;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Communication\Application\DTOs\EmailPaymentInformation;
use Contexis\Events\Payment\Application\Contracts\PaymentQrGenerator;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;

final readonly class ResolveEmailPaymentInformation
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private GatewayRepository $gatewayRepository,
        private ResolveBookingPaymentLink $resolveBookingPaymentLink,
        private PaymentQrGenerator $paymentQrGenerator,
        private Clock $clock,
    ) {
    }

    public function execute(Booking $booking): ?EmailPaymentInformation
    {
        if ($booking->id === null || $booking->priceSummary->isFree()) {
            return null;
        }

        $gatewayId = trim((string) $booking->gateway);
        if ($gatewayId === '') {
            return null;
        }

        $gateway = $this->gatewayRepository->find($gatewayId);
        if ($gateway === null) {
            return null;
        }

        $transaction = $gateway->supportsCheckoutLink()
            ? $this->resolveOnlineTransaction($booking)
            : $this->findPendingOfflineTransaction($booking, $gatewayId);

        if ($transaction === null) {
            return null;
        }

        return new EmailPaymentInformation(
            transaction: $transaction,
            gatewayTitle: $gateway->getTitle(),
            qrCodePng: $transaction->isOffline()
                ? $this->createQrCode($transaction, $booking->reference->toString())
                : null,
        );
    }

    private function resolveOnlineTransaction(Booking $booking): ?Transaction
    {
        try {
            return $this->resolveBookingPaymentLink->execute($booking->reference->toString());
        } catch (\DomainException) {
            return null;
        }
    }

    private function findPendingOfflineTransaction(Booking $booking, string $gatewayId): ?Transaction
    {
        $transactions = $this->transactionRepository->findByBookingId($booking->id)->toArray();
        usort($transactions, static fn (Transaction $left, Transaction $right): int => $right->createdAt <=> $left->createdAt);

        foreach ($transactions as $transaction) {
            if ($transaction->gateway !== $gatewayId || !$transaction->isOffline()) {
                continue;
            }

            if ($transaction->status !== TransactionStatus::PENDING || $transaction->hasExpiredAt($this->clock->now())) {
                continue;
            }

            return $transaction;
        }

        return null;
    }

    private function createQrCode(Transaction $transaction, string $reference): ?string
    {
        try {
            $dataUri = $this->paymentQrGenerator->generate($transaction, $reference, 'png');
        } catch (\DomainException) {
            return null;
        }

        $encoded = strstr($dataUri, ',');
        if ($encoded === false) {
            return null;
        }

        $content = base64_decode(substr($encoded, 1), true);

        return $content === false ? null : $content;
    }
}
