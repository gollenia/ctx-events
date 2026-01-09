<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface HasHooks
{
    public function registerHooks(): void;
}
