<?php

namespace Contexis\Events\Presentation\Requests;

use Contexis\Events\Core\Contracts\Request;

final class ListEventsRequest {

    public function __construct(
        public readonly int $page = 0,
        public readonly int $perPage = -1,
		public readonly array $include = [],
		public readonly string $orderBy = 'date-time',
        public readonly string $order = 'DESC',
        public readonly ?string $scope,
        public readonly array $categories = [],
        public readonly array $tags = [],
        public readonly int $location = 0,
        public readonly array $persons = [],
        public readonly bool $bookable = false,
		public readonly bool $availibility = false,
		public readonly ?string $search
        
    ) {}

	public static function fromParams($params) {
		return new self(
			page: $params['page'] ?? 0,
			perPage: $params['per_page'] ?? -1,
			include: $params['include'] ?? "",
			orderBy: $params['order_by'] ?? 'date-time',
			order: $params['order'] ?? 'DESC',
			scope: $params['scope'] ?? null,
			categories: $params['categories'] ?? [],
			tags: $params['tags'] ?? [],
			location: $params['location'] ?? 0,
			persons: $params['persons'] ?? [],
			bookable: $params['bookable'] ?? false,
			availibility: $params['availibility'] ?? false,
			search: $params['search'] ?? null
		);
	}
}

