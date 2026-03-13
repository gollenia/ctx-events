<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Contracts;

use Contexis\Events\Shared\Domain\ValueObjects\Actor;

interface CurrentActorProvider
{
    public function current(): Actor;
}
