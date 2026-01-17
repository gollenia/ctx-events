<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Gateways\Mollie;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\PaymentGateway;
use Contexis\Events\Payment\Domain\Transaction;

use Mollie\Api\MollieApiClient;
use Uri\Rfc3986\Uri;

final class MollieGateway implements PaymentGateway
{
    private ?MollieApiClient $client = null;
	private MollieConfiguration $config;

	public function __construct() {
		$this->config = new MollieConfiguration();
	}
    public function getId(): string
    {
        return 'mollie';
    }

	public function getAdminName(): string {
		return __('Mollie', 'ctx-events');
	}

	public function getTitle(): string {
		return $this->config->title;
	}

    public array $transaction_detail = [
        'Mollie Dashboard',
        'https://www.mollie.com/dashboard/payments/%s',
        'https://www.mollie.com/dashboard/payments/%s'
    ];

    public function verifyPayment(Transaction $transaction): Transaction
	{
		return $transaction->complete();
	}

    private function getClient(): ?MollieApiClient
    {
        if ($this->client !== null) {
            return $this->client;
        }

        $instance = new \Mollie\Api\MollieApiClient();
        $instance->setApiKey($this->config->getApiKey());
        $this->client = $instance;
        return $instance;
    }

    public function initiatePayment(Booking $booking): Transaction
    {
        if ($booking->priceSummary->isFree()) {
            throw new \InvalidArgumentException('Cannot initiate payment for free booking.');
        }

        $mollieInstance = $this->getClient();
        $molliePayment = $mollieInstance->payments->create(
            [
                'amount' => [
                    'currency' => $booking->priceSummary->finalPrice->currency,
                    'value' => $booking->priceSummary->finalPrice->toFloat(),
                ],
                'description' => sprintf('Booking #%s', $booking->id),
                'redirectUrl' => site_url('/wp-json/events/v3/bookings/return?booking_uuid=' . $booking->uuid),
                'webhookUrl' => site_url('/payment-webhook'),
                'locale' => get_locale(),
                'metadata' => [
                    'booking_uuid' => (string) $booking->uuid,
                    'customer_name' => (string) $booking->name,
                    'customer_email' => (string) $booking->email,
                ],
                'sequenceType' => 'oneoff',
            ]
        );

        return Transaction::forPaymentService(
            bookingId: $booking->id,
            amount: $booking->priceSummary->finalPrice,
            externalId: $molliePayment->id,
            checkoutUrl: Uri::parse($molliePayment->getCheckoutUrl()),
			gatewayUrl: Uri::parse('https://www.mollie.com/dashboard/payments/' . $molliePayment->id),
            gateway: 'mollie'
        );
    }

	public function updateSettings(array $settings): void {
		$this->config->updateFromArray($settings);
	}

	public function save(): void {
		$this->config->save();
	}

	public function getSettingsSchema(): array {
		return $this->config->getFormSchema();
	}

	public function isActive(): bool {
		return $this->config->isEnabled;
	}

	public function setActive(bool $active): void {
		$this->config->setActive($active);
	}
}
