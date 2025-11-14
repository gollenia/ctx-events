<?php

namespace Contexis\Events\Event\Presentation;

use Contexis\Events\Event\Application\EventCriteria;
use Contexis\Events\Event\Domain\TimeScope;
use Contexis\Events\Shared\Presentation\Contracts\CriteriaMapper;
use WP_REST_Request;

final class EventCriteriaMapper implements CriteriaMapper
{
    public static function fromRequest(WP_REST_Request $request): EventCriteria
    {
        return new EventCriteria(
            page: $request->get_param('page') ?? 0,
            perPage: $request->get_param('per_page') ?? -1,
            include: $request->get_param('include') ?? [],
            orderBy: $request->get_param('order_by') ?? 'date-time',
            order: $request->get_param('order') ?? 'DESC',
            scope: TimeScope::from($request->get_param('scope')),
            categories: $request->get_param('categories') ?? [],
            tags: $request->get_param('tags') ?? [],
            location: $request->get_param('location') ?? null,
            person: $request->get_param('person') ?? null,
            bookable: $request->get_param('bookable') ?? false,
            availibility: $request->get_param('availibility') ?? false,
            search: $request->get_param('search') ?? null
        );
    }
}
