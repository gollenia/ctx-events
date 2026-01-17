<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Gateways\Offline;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\PaymentGateway;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\ValueObjects\BankData;
use Contexis\Events\Payment\Infrastructure\Contracts\GatewayConfiguration;

use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Uri\Rfc3986\Uri;

final class OfflineGateway implements PaymentGateway
{
	private OfflineConfiguration $config;

	public function __construct() {
		$this->config = new OfflineConfiguration();
	}

	public function getId(): string {
		return 'offline';
	}

	public function getAdminName(): string {
		return __('Bank Transfer', 'ctx-events');
	}

	public function getTitle(): string {
		return $this->config->title;
	}

	public function verifyPayment(Transaction $transaction): Transaction
	{
		return $transaction->complete();
	}

	public function getSettingsSchema(): array {
		return $this->config->getFormSchema();
	}

	public function updateSettings(array $settings): void {
		$this->config->updateFromArray($settings);
	}

	public function save(): void {
		$this->config->save();
	}

    public function initiatePayment(Booking $booking): Transaction
    {
        return Transaction::forBankTransfer(
            $booking->id,
            $booking->priceSummary->finalPrice,
            'offline',
            $this->config->bankData
        );
    }

	public function setActive(bool $active): void
	{
		$this->config->setActive($active);
	}

	public function isActive(): bool
	{
		return $this->config->isEnabled;
	}
}
