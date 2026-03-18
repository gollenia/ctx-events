<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Gateways\Mollie;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Payment\Domain\PaymentGateway;
use Contexis\Events\Payment\Domain\Transaction;

use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment as MolliePayment;
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

	public function getDescription(): ?string {
		return $this->config->description;
	}

    public array $transaction_detail = [
        'Mollie Dashboard',
        'https://www.mollie.com/dashboard/payments/%s',
        'https://www.mollie.com/dashboard/payments/%s'
    ];

    public function verifyPayment(Transaction $transaction): Transaction
	{
        $externalId = (string) $transaction->externalId;
        if ($externalId === '') {
            throw new \DomainException('Cannot verify Mollie payment without external ID.');
        }

        $payment = $this->getClient()->payments->get($externalId);

        return $this->mapMolliePaymentToTransaction($transaction, $payment)
            ->withExpiresAt($this->resolveExpiresAt($payment));
	}

    private function getClient(): MollieApiClient
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
        $reference = $booking->reference->toString();
        $molliePayment = $mollieInstance->payments->create(
            [
                'amount' => [
                    'currency' => $booking->priceSummary->finalPrice->currency->toString(),
                    'value' => $booking->priceSummary->finalPrice->toFloat(),
                ],
                'description' => sprintf('Booking #%s', $reference),
                'redirectUrl' => $this->config->returnUrl !== ''
                    ? $this->config->returnUrl
                    : site_url('/wp-json/events/v3/bookings/return?booking_uuid=' . $reference),
                'locale' => get_locale(),
                'metadata' => [
                    'booking_uuid' => $reference,
                    'customer_name' => $booking->name->toString(),
                    'customer_email' => $booking->email->toString(),
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
            gateway: 'mollie',
            expiresAt: $this->resolveExpiresAt($molliePayment),
        );
    }

    private function resolveExpiresAt(object $molliePayment): ?\DateTimeImmutable
    {
        $value = null;

        if (method_exists($molliePayment, 'getExpiresAt')) {
            $value = $molliePayment->getExpiresAt();
        } elseif (isset($molliePayment->expiresAt)) {
            $value = $molliePayment->expiresAt;
        }

        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value);
        }

        if (is_string($value) && $value !== '') {
            return new \DateTimeImmutable($value);
        }

        return null;
    }

    private function mapMolliePaymentToTransaction(Transaction $transaction, MolliePayment $payment): Transaction
    {
        if ($payment->hasChargebacks() || $payment->hasRefunds()) {
            return $transaction->refund();
        }

        if ($payment->isPaid()) {
            return $transaction->complete();
        }

        if ($payment->isExpired()) {
            return $transaction->expire();
        }

        if ($payment->isCanceled()) {
            return $transaction->cancel();
        }

        if ($payment->isFailed()) {
            return $transaction->fail();
        }

        return $transaction->pend();
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

	public function isEnabled(): bool {
		return $this->config->isEnabled;
	}

	public function setEnabled(bool $active): void {
		$this->config->setEnabled($active);
	}

	public function isValid(): bool {
		return $this->config->isValid();
	}

    public function supportsCheckoutLink(): bool
    {
        return true;
    }
}
