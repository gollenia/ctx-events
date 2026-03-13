<?php

declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;
use Contexis\Events\Shared\Domain\ValueObjects\Actor;

final readonly class FakeCurrentActorProvider implements CurrentActorProvider
{
    public function __construct(
        private Actor $actor = new Actor(0, ''),
    ) {
    }

    public function current(): Actor
    {
        return $this->actor;
    }
}
