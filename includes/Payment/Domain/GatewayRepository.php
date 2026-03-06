<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

interface GatewayRepository
{
    public function find(string $id): ?PaymentGateway;
    public function findAll(): GatewayCollection;
    public function findActive(): GatewayCollection;
}
