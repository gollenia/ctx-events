<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Payment\Application\Dtos\EditGatewayDto;
use Contexis\Events\Payment\Domain\GatewayRepository;

final class EditGateway
{
    public function __construct(
        private GatewayRepository $gatewayRepository
    ) {
    }

    public function execute(string $id): ?EditGatewayDto
    {
        $gateway = $this->gatewayRepository->find($id);

        if (!$gateway) {
            return null;
        }

        return EditGatewayDto::fromPaymentGateway($gateway);
    }
}
