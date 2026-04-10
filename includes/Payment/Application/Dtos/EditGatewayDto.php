<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Dtos;

use Contexis\Events\Payment\Domain\PaymentGateway;

final class EditGatewayDto
{
	/**
	 * @param array<string, mixed> $settings
	 */
    public function __construct(
        public string $id,
        public string $name,
        public array $settings
    ) {
    }

    public static function fromPaymentGateway(PaymentGateway $gateway): self
    {
        return new self(
            id: $gateway->getId(),
            name: $gateway->getAdminName(),
            settings: $gateway->getSettingsSchema()
        );
    }
}
