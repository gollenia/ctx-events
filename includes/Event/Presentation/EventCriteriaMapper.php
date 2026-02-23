<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Presentation;

use Contexis\Events\Event\Application\DTOs\EventCriteria;
use Contexis\Events\Event\Application\DTOs\EventIncludeRequest;
use Contexis\Events\Event\Domain\Enums\TimeScope;
use Contexis\Events\Event\Domain\TicketScope;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Infrastructure\ValueObjects\Order;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;
use Contexis\Events\Shared\Presentation\Contracts\CriteriaMapper;
use WP_REST_Request;

final class EventCriteriaMapper implements CriteriaMapper
{
    public static function fromRequest(WP_REST_Request $request, UserContext $userContext): EventCriteria
    {
		return new EventCriteria(
            page: $request->get_param('page') ?? 0,
            perPage: $request->get_param('per_page') ?? -1,
            status: self::getStatusList($request->get_param('status'), $userContext->isAdmin()),
            orderBy: OrderBy::fromField($request->get_param('order_by') ?? 'date', Order::from($request->get_param('order') ?? 'DESC')),
            scope: TimeScope::from($request->get_param('scope')),
            categories: $request->get_param('categories') ?? [],
            tags: $request->get_param('tags') ?? [],
            location: $request->get_param('location') ?? null,
            person: $request->get_param('person') ?? null,
            isFree: $request->get_param('is_free') ?? null,
            bookable: $request->has_param('bookable') ? $request->get_param('bookable') : null,
            search: $request->get_param('search') ?? null
        );
    }

    private static function getStatusList(?array $statusParam, bool $isAdmin): StatusList
    {
        if (!$isAdmin) {
            return \Contexis\Events\Shared\Domain\ValueObjects\StatusList::public();
        }

        if ($statusParam !== null) {
            return StatusList::fromStrings($statusParam);
        }

        return \Contexis\Events\Shared\Domain\ValueObjects\StatusList::defaultAdmin();
    }
}
