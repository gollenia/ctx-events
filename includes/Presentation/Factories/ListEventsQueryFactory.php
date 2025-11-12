<?php

namespace Contexis\Events\Presentation\Factories;

use Contexis\Events\Application\Query\ListEventsQuery;
use Contexis\Events\Domain\ValueObjects\EventScope;
use WP_REST_Request;

final class ListEventsQueryFactory {

	public static function fromWpRequest(WP_REST_Request $request): ListEventsQuery {
		return new ListEventsQuery(
			page: $request->get_param('page') ?? 0,
			perPage: $request->get_param('per_page') ?? -1,
			include: $request->get_param('include') ?? "",
			orderBy: $request->get_param('order_by') ?? 'date-time',
			order: $request->get_param('order') ?? 'DESC',
			scope: EventScope::from($request->get_param('scope')),
			categories: $request->get_param('categories') ?? [],
			tags: $request->get_param('tags') ?? [],
			location: $request->get_param('location') ?? 0,
			persons: $request->get_param('persons') ?? [],
			bookable: $request->get_param('bookable') ?? false,
			availibility: $request->get_param('availibility') ?? false,
			search: $request->get_param('search') ?? null
		);
	}
}

