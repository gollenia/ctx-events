<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Payment\Application\Dtos\GatewayListItemDto;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\PaymentGateway;

final class ListGateways
{
    public function __construct(private GatewayRepository $repository)
    {
    }

    /**
    * @return array<GatewayListItemDto>
    */
    public function execute(): array
    {
        $list = $this->repository->findAll();
        return array_map(function (PaymentGateway $gateway) {
            return GatewayListItemDto::fromPaymentGateway($gateway);
        }, $list->toArray());
    }
}
