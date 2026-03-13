<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Payment\Domain\PaymentGateway;
use Contexis\Events\Payment\Domain\Transaction;

final class FakePaymentGateway implements PaymentGateway
{
    public function __construct(
        private string $id = 'fake',
        private bool $enabled = true,
        private bool $valid = true,
        private ?\Closure $verifyPaymentUsing = null,
        private ?\Closure $initiatePaymentUsing = null,
    ) {
    }

    public function getId(): string { return $this->id; }
    public function getAdminName(): string { return 'Fake Gateway'; }
    public function getTitle(): string { return 'Fake'; }
    public function getDescription(): ?string { return null; }
    public function isEnabled(): bool { return $this->enabled; }
    public function isValid(): bool { return $this->valid; }
    public function setEnabled(bool $active): void { $this->enabled = $active; }
    public function save(): void {}
    public function getSettingsSchema(): array { return []; }
    public function updateSettings(array $settings): void {}

    public function initiatePayment(Booking $booking): Transaction
    {
        if ($this->initiatePaymentUsing !== null) {
            return ($this->initiatePaymentUsing)($booking);
        }

        throw new \RuntimeException('Not implemented in FakePaymentGateway');
    }

    public function verifyPayment(Transaction $transaction): Transaction
    {
        if ($this->verifyPaymentUsing !== null) {
            return ($this->verifyPaymentUsing)($transaction);
        }

        throw new \RuntimeException('Not implemented in FakePaymentGateway');
    }
}
