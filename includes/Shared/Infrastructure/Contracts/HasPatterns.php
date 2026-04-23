<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface HasPatterns
{
    public function registerPatterns(): void;
}
