<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\ValueObjects\BankData;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Uri\Rfc3986\Uri;

final readonly class Transaction
{
    public function __construct(
        public ?TransactionId $id,
        public BookingId $bookingId,
        public Price $amount,
        public string $gateway,
        public TransactionStatus $status,
        public \DateTimeImmutable $createdAt,
        public ?string $externalId = null,
        public ?BankData $bankData = null,
		public string $instructions = '',
        public ?Uri $checkoutUrl = null,
		public ?Uri $gatewayUrl = null,
        public ?\DateTimeImmutable $expiresAt = null,
    ) {
    }

    public static function forBankTransfer(
        BookingId $bookingId,
        Price $amount,
        string $gateway,
        ?BankData $bankData = null,
		string $instructions = '',
        ?\DateTimeImmutable $expiresAt = null,
    ): self {
        return new self(
            id: null, 
            bookingId: $bookingId,
            amount: $amount,
            gateway: $gateway,
            status: TransactionStatus::PENDING,
            bankData: $bankData,
			instructions: $instructions,
            createdAt: new \DateTimeImmutable(),
            expiresAt: $expiresAt,
        );
    }

    public static function forPaymentService(
        BookingId $bookingId,
        Price $amount,
        string $externalId,
        Uri $checkoutUrl,
        string $gateway,
		Uri $gatewayUrl,
		string $instructions = '',
        ?\DateTimeImmutable $expiresAt = null,
    ): self {
        return new self(
            id: null,
            bookingId: $bookingId,
            amount: $amount,
            gateway: $gateway,
            status: TransactionStatus::PENDING,
            externalId: $externalId,
            checkoutUrl: $checkoutUrl,
			gatewayUrl: $gatewayUrl,
			instructions: $instructions,
            createdAt: new \DateTimeImmutable(),
            expiresAt: $expiresAt,
        );
    }

    public function expire()
    {
        return clone($this, [
            "status" => TransactionStatus::EXPIRED
        ]);
    }

    public function cancel()
    {
        return clone($this, [
            "status" => TransactionStatus::CANCELED
        ]);
    }

    public function fail()
    {
        return clone($this, [
            "status" => TransactionStatus::FAILED
        ]);
    }

    public function refund()
    {
        return clone($this, [
            "status" => TransactionStatus::REFUNDED
        ]);
    }

    public function complete()
    {
        return clone($this, [
            "status" => TransactionStatus::PAID
        ]);
    }

    public function pend(): self
    {
        return clone($this, [
            "status" => TransactionStatus::PENDING
        ]);
    }

    public function isOffline(): bool
    {
        return $this->checkoutUrl === null;
    }

	public function setAmount(Price $amount): self
	{
		return clone($this, [
			"amount" => $amount
		]);
	}

    public function withExpiresAt(?\DateTimeImmutable $expiresAt): self
    {
        return clone($this, [
            "expiresAt" => $expiresAt
        ]);
    }

    public function withCreatedAt(\DateTimeImmutable $createdAt): self
    {
        return clone($this, [
            "createdAt" => $createdAt
        ]);
    }

    public function hasExpiredAt(\DateTimeImmutable $now): bool
    {
        return $this->expiresAt !== null && $this->expiresAt <= $now;
    }
}
