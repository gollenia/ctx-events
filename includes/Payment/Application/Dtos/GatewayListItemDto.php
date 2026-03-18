<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Dtos;

use Contexis\Events\Payment\Domain\PaymentGateway;

final class GatewayListItemDto
{
    public function __construct(
        public string $slug,
        public string $adminName,
        public string $title,
		public ?string $description,
        public bool $active,
		public bool $isValid,
        public bool $supportsCheckoutLink,
    ) {}

	static function fromPaymentGateway(PaymentGateway $gateway): self
	{
		return new self(
			$gateway->getId(),
			$gateway->getAdminName(),
			$gateway->getTitle(),
			$gateway->getDescription(),
			$gateway->isEnabled(),
			$gateway->isValid(),
            $gateway->supportsCheckoutLink()
		);
	}

    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'adminName' => $this->adminName,
            'title' => $this->title,
			'description' => $this->description,
            'active' => $this->active,
			'isValid' => $this->isValid,
            'supportsCheckoutLink' => $this->supportsCheckoutLink,
        ];
    }
}
