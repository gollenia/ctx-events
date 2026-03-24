<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Payment\Application\Dtos\PaymentQrResponse;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Payment\Infrastructure\SepaPaymentQrGenerator;

final class GetBookingPaymentQr
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private TransactionRepository $transactionRepository,
        private SepaPaymentQrGenerator $qrGenerator,
    ) {
    }

    public function execute(string $bookingReference, string $format = 'svg'): PaymentQrResponse
    {
        $booking = $this->bookingRepository->findByReference($bookingReference);

        if ($booking === null || $booking->id === null) {
            throw new \DomainException('Booking not found.');
        }

        $transaction = $this->transactionRepository->findLatestByBookingId($booking->id);

        if ($transaction === null) {
            throw new \DomainException('Payment transaction not found.');
        }

        if ($transaction->gateway !== 'offline' || !$transaction->isOffline()) {
            throw new \DomainException('Payment QR is only available for offline payments.');
        }

        $reference = trim((string) ($transaction->bankData?->reference ?? ''));
        $reference = $reference !== '' ? $reference : $booking->reference->toString();

        return PaymentQrResponse::from(
            gateway: $transaction->gateway,
            format: $format,
            mimeType: $this->qrGenerator->mimeType($format),
            dataUri: $this->qrGenerator->generate($transaction, $reference, $format),
        );
    }
}
