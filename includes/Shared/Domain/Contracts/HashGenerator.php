<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Contracts;

interface HashGenerator
{
    public function sign(string $payload): string;

    public function verify(string $payload, string $signature): bool;
}
