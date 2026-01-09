<?php
declare(strict_types = 1);

namespace Contexis\Events\Event\Domain\Signals;

use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\Abstract\Signal;

class TicketPriceChangedSignal extends Signal
{
    public const NAME = 'ctx.event.price.changed';

    public function __construct(
        public readonly EventId $eventId
    ) {
        parent::__construct();
    }
}