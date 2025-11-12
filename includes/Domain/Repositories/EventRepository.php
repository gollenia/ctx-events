<?php

namespace Contexis\Events\Domain\Repositories;

use Contexis\Events\Core\Contracts\QueryOptions;
use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Application\Security\ViewContext;
use Contexis\Events\Application\Query\ListEventsQuery;
use Contexis\Events\Application\Requests\EventPageRequest;
use Contexis\Events\Domain\ValueObjects\Id\EventId;

interface EventRepository
{
    public function find(?EventId $id): ?Event;
	public function get(?EventId $id): Event;
    public function first(EventPageRequest $request, ViewContext $viewContext): ?Event;
    public function search(EventPageRequest $request, ViewContext $viewContext): \Contexis\Events\Domain\Collections\EventCollection;
    public function count(EventPageRequest $request, ViewContext $viewContext): int;
}
