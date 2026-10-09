<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Presentation;

use Contexis\Events\Event\Application\DTOs\EventCriteria;
use Contexis\Events\Event\Domain\ValueObjects\EventFilters;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
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
            orderBy: OrderBy::fromField(
                $request->get_param('order_by') ?? 'date',
                self::orderFromRequest($request->get_param('order')),
            ),
            scope: self::scopeFromRequest($request->get_param('scope')),
            isFree: $request->get_param('is_free') ?? null,
            bookable: $request->has_param('bookable') ? $request->get_param('bookable') : null,
            filters: new EventFilters(
                categories: $request->get_param('categories') ?? [],
                tags: $request->get_param('tags') ?? [],
                locationId: $request->get_param('location') ?? null,
                personId: $request->get_param('person') ?? null,
                search: $request->get_param('search') ?? null,
            ),
            recurrenceId: self::recurrenceIdFromRequest($request->get_param('recurring_event')),
        );
    }

    private static function orderFromRequest(mixed $value): Order
    {
        if (!is_string($value)) {
            return Order::DESC;
        }

        return Order::tryFrom(strtolower($value)) ?? Order::DESC;
    }

    private static function scopeFromRequest(mixed $value): TimeScope
    {
        return is_string($value)
            ? TimeScope::tryFrom($value) ?? TimeScope::FUTURE
            : TimeScope::FUTURE;
    }

    private static function recurrenceIdFromRequest(mixed $value): ?RecurrenceId
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        return $id !== false && $id > 0 ? new RecurrenceId($id) : null;
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
