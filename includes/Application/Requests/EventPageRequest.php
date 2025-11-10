<?php

namespace Contexis\Events\Application\Requests;

use Contexis\Events\Core\Contracts\Request;

final class ListEventsRequest {
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?string $search,
        public readonly ?string $scope,
        public readonly array $categories = [],
        public readonly array $tags = [],
        public readonly array $locations = [],
        public readonly array $persons = [],
        public readonly ?string $bookable = null,
        public readonly string $orderBy = 'date-time',
        public readonly string $order = 'DESC',
    ) {}
}

