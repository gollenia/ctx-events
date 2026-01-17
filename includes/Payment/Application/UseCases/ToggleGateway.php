<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Payment\Application\Dtos\GatewayListItemDto;
use Contexis\Events\Payment\Domain\GatewayRepository;

final class ToggleGateway
{
	public function __construct(
		private readonly GatewayRepository $gatewayRepository,
	) {}
	public function execute(string $slug, bool $switch): ?GatewayListItemDto
	{
		$gateway = $this->gatewayRepository->get($slug);
		if(!$gateway) {
			return null;
		}
		
		$this->gatewayRepository->update($gateway);
		return GatewayListItemDto::fromPaymentGateway($gateway);
	}
}