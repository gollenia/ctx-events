<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Services;

use Contexis\Events\Payment\Domain\GatewayRepository;

final class GatewayService
{
    private ?array $nameMap = null;

    public function __construct(private GatewayRepository $gatewayRepository)
    {
    }

    public function findNameBySlug(string $slug): string
    {
        return $this->getNameMap()[$slug] ?? $slug;
    }

    private function getNameMap(): array
    {
        if ($this->nameMap !== null) {
            return $this->nameMap;
        }

        $this->nameMap = [];
        foreach ($this->gatewayRepository->findAll() as $gateway) {
            $this->nameMap[$gateway->getId()] = $gateway->getAdminName();
        }

        return $this->nameMap;
    }
}
