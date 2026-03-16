<?php

declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class HookRegistrar implements Registrar
{
    /** @param iterable<Registrar> $registrars */
    public function __construct(private readonly iterable $registrars)
    {
    }

    public function hook(): void
    {
        foreach ($this->registrars as $registrar) {
            $registrar->hook();
        }
    }
}
