<?php

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Domain\TimeScope;
use Contexis\Events\Shared\Application\Contracts\Criteria;

/**
 * Class EventCriteria
 *
 * Represents the criteria for querying events.
 * @
 */
final class EventCriteria implements Criteria
{
    public function __construct(
        public readonly int $page = 0,
        public readonly int $perPage = 10,
        public readonly array $status = ['publish'],
        public readonly array $include = [],
        public readonly string $orderBy = 'date-time',
        public readonly string $order = 'DESC',
        public readonly TimeScope $scope = TimeScope::FUTURE,
        public readonly array $categories = [],
        public readonly array $tags = [],
        public readonly ?int $location = null,
        public readonly ?int $person = null,
        public readonly bool $bookable = false,
        public readonly bool $availibility = false,
        public readonly ?string $search = null
    ) {
    }
}
