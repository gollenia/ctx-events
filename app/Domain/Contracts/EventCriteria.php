<?php

namespace Contexis\Events\Domain\Contracts;

use Contexis\Events\Domain\ValueObjects\EventScope;

final class EventCriteria
{
	 /** @var string[] slugs */
    public array $categories = [];
    /** @var int[] term_ids */
    public array $tags = [];
    /** @var int[] speaker ids */
    public array $speakers = [];
    /** @var string[] post status */
    public array $statuses = [];

    public EventScope $scope = EventScope::FUTURE;
    public ?int $locationId = null;

    public bool $bookableOnly = false;
    public ?string $search = null;

    /** @var int[] */
    public array $excludeIds = [];

    public bool $recurringOnly = false;

    /** orderBy: 'date-time'|'booking-date'|'booking'|'location'|'title' ... */
    public string $orderBy = 'date-time';
    public string $order = 'DESC';

    public int $limit = 20;
    public int $page = 1;     // WordPress mag 'paged' lieber als offset
    public ?int $offset = null; // optional, wenn du es brauchst

    /** Lazy/Eager-Loading Wünsche */
    public bool $withLocation = false;
    public bool $withOrganizer = false;
    public bool $withTags = false;
    public bool $withMeta = true; // falls du viel Meta brauchst
}