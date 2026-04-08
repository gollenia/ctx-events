<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Icons;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class IconRegistryBootstrap implements Registrar
{
    public function __construct(
        private readonly IconRegistry $registry
    ) {}

    public function hook(): void
    {
        $this->registry->boot();
    }
}
