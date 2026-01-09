<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Abstract;

use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

abstract class Signal extends SymfonyEvent
{
    public readonly \DateTimeImmutable $occurredOn;

	public const NAME = '';

    public function __construct()
    {
        $this->occurredOn = new \DateTimeImmutable();
    }
}