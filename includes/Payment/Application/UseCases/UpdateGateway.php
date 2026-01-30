<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Payment\Application\Dtos\GatewayListItemDto;
use Contexis\Events\Payment\Domain\GatewayRepository;

final class UpdateGateway
{
    public function __construct(
        private readonly GatewayRepository $gatewayRepository,
    ) {
    }
    public function execute(string $slug, array $data): ?GatewayListItemDto
    {
        $gateway = $this->gatewayRepository->find($slug);
        if (!$gateway) {
            return null;
        }

        $gateway->updateSettings($data);
        $gateway->save($gateway);
        return GatewayListItemDto::fromPaymentGateway($gateway);
    }
}
