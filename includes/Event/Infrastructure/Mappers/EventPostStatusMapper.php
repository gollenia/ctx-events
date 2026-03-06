<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure\Mappers;

use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Shared\Domain\ValueObjects\Status;

final class EventPostStatusMapper
{
    public static function fromPost(string $wpStatus): EventStatus
    {
        return match ($wpStatus) {
            'publish' => EventStatus::Published,
            'private' => EventStatus::Private,
            'trash'   => EventStatus::Trash,
            'draft'   => EventStatus::Draft,
			'cancelled' => EventStatus::Cancelled,
            default   => EventStatus::Draft,
        };
    }

    public static function toPost(EventStatus $status): string
    {
        return match ($status) {
            EventStatus::Published      => 'publish',
            EventStatus::Private        => 'private',
            EventStatus::Trash        => 'trash',
            EventStatus::Draft          => 'draft',
            EventStatus::Cancelled      => 'cancelled',
        };
    }
}
