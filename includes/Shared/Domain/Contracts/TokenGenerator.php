<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Contracts;

interface TokenGenerator
{
    public function generate(int $bytes = 32): string;
}
