<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Dtos;

use Contexis\Events\Payment\Domain\PaymentGateway;
use Contexis\Events\Payment\Domain\Enums\GatewayStatus;

final class GatewayListItemDto
{
    public function __construct(
        public string $slug,
        public string $adminName,
        public string $title,
        public ?string $description,
        public bool $active,
        public GatewayStatus $status,
        public bool $supportsCheckoutLink,
    ) {
    }

    public static function fromPaymentGateway(PaymentGateway $gateway): self
    {
        return new self(
            $gateway->getId(),
            $gateway->getAdminName(),
            $gateway->getTitle(),
            $gateway->getDescription(),
            $gateway->isEnabled(),
            $gateway->getStatus(),
            $gateway->supportsCheckoutLink()
        );
    }

	/**
	 * @return array<string, mixed>
	 */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'adminName' => $this->adminName,
            'title' => $this->title,
            'description' => $this->description,
            'active' => $this->active,
            'status' => $this->status->value,
            'supportsCheckoutLink' => $this->supportsCheckoutLink,
        ];
    }
}
