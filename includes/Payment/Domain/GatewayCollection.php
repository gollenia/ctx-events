<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class GatewayCollection extends Collection
{
	public static function from(PaymentGateway ...$gateways): self
	{
		return new self($gateways);
	}

	public function removeBySlug(array $slugs): self
	{
		$filtered = array_filter($this->items, function (PaymentGateway $gateway) use ($slugs) {
			return !in_array($gateway->slug, $slugs, true);
		});
		return new self(...$filtered);
	}
}