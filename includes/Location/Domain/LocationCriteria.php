<?php

namespace Contexis\Events\Location\Domain;

use Contexis\Events\Shared\Application\Contracts\Criteria;

final class LocationCriteria implements Criteria
{
    public function __construct(
        public readonly int $page = 0,
        public readonly int $perPage = 10,
        public readonly array $status = ['publish'],
        public readonly array $include = [],
        public readonly array $categories = [],
        public readonly array $tags = [],
        public readonly ?string $orderBy = null,
        public readonly ?string $order = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $search = null
    ) {
    }
}
