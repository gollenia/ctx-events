<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

interface GatewayRepository
{
    public function get(string $id): ?PaymentGateway;
    public function findAll(): array;
    public function findActive(): array;
}
