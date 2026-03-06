<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Contracts;

interface SessionHashResolver
{
    public function resolve(): string;
}
