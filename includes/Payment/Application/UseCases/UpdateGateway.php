<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Payment\Application\Dtos\EditGatewayDto;
use Contexis\Events\Payment\Domain\GatewayRepository;

final class UpdateGateway
{
    public function __construct(
        private readonly GatewayRepository $gatewayRepository,
    ) {
    }

	/**
	 * @param array<string, mixed> $settings
	 */
    public function execute(string $slug, array $settings): ?EditGatewayDto
    {
        $gateway = $this->gatewayRepository->find($slug);
        if (!$gateway) {
            return null;
        }

        $gateway->updateSettings($settings);
        $gateway->save();
        return EditGatewayDto::fromPaymentGateway($gateway);
    }
}
