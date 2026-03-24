<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\Contracts\EventMailTemplateOverrideStore;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Infrastructure\EventMeta;

final class WpEventMailTemplateOverrideStore implements EventMailTemplateOverrideStore
{
    public function eventMailTemplateOverrides(EventId $eventId): array
    {
        $value = get_post_meta($eventId->toInt(), EventMeta::BOOKING_MAILS, true);

        if (!is_array($value)) {
            return [];
        }

        $normalized = [];

        foreach ($value as $override) {
            if (!is_array($override) || !is_string($override['key'] ?? null)) {
                continue;
            }

            $normalized[$override['key']] = $override;
        }

        return $normalized;
    }
}
