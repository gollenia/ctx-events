<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface HasMetaData
{
    public function registerMeta(): void;
}
