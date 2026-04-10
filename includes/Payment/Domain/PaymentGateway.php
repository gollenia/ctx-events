<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Payment\Domain\Transaction;

interface PaymentGateway
{
    public function verifyPayment(Transaction $transaction): Transaction;
    public function initiatePayment(Booking $booking): Transaction;
    public function getSettingsSchema(): array;
    public function updateSettings(array $settings): void;
    public function getId(): string;
    public function getAdminName(): string;
    public function getTitle(): string;
    public function getDescription(): ?string;
    public function setEnabled(bool $active): void;
    public function isEnabled(): bool;
    public function isValid(): bool;
    public function supportsCheckoutLink(): bool;
    public function save(): void;
}
