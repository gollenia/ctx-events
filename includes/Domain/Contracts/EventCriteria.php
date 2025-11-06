<?php

namespace Contexis\Events\Domain\Contracts;

use Contexis\Events\Domain\ValueObjects\EventScope;
use Contexis\Events\Core\Contracts\Criteria;

final class EventCriteria implements Criteria
{
    public array $categories = [];
    public array $tags = [];
    public array $speakers = [];
    public array $statuses = [];

    public EventScope $scope = EventScope::FUTURE;
    public ?int $locationId = null;

    public bool $bookableOnly = false;
    public ?string $search = null;

    public array $excludeIds = [];

    public bool $recurringOnly = false;

    public string $orderBy = 'date-time';
    public string $order = 'DESC';

    public int $limit = 20;
    public int $page = 1;     
    public ?int $offset = null; 
    public bool $withLocation = false;
    public bool $withOrganizer = false;
    public bool $withTags = false;
    public bool $withMeta = true; 
}
