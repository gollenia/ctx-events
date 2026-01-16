<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Contracts;

interface GatewayConfiguration
{
    public function updateFromArray(array $data): void;
    public function getFormSchema(): array;
}
