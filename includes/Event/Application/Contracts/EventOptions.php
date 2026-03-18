<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\Contracts;

use Contexis\Events\Shared\Domain\Contracts\Options;

interface EventOptions extends Options
{
    public function publicShowPastEvents(): bool;

    public function ongoingEventsArePast(): bool;

	public function getEventsSlug(): string;
	public function getIconVariant(): string;
}
