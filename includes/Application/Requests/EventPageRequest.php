<?php

namespace Contexis\Events\Application\Requests;

use Contexis\Events\Core\Contracts\QueryRequest;
use Contexis\Events\Domain\ValueObjects\EventScope;

final class EventPageRequest {
    public function __construct(
        public readonly int $page = 0,
        public readonly int $perPage = 10,
		public readonly array $include = [],
		public readonly string $orderBy = 'date-time',
        public readonly string $order = 'DESC',
        public readonly EventScope $scope = EventScope::FUTURE,
        public readonly array $categories = [],
        public readonly array $tags = [],
        public readonly int $location = 0,
        public readonly array $persons = [],
        public readonly bool $bookable = false,
		public readonly bool $availibility = false,
		public readonly ?string $search
        
    ) {}
}

