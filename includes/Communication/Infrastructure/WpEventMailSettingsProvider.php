<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\Contracts\EventMailSettingsProvider;
use Contexis\Events\Communication\Domain\ValueObjects\EventMailSettings;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Infrastructure\EventMeta;

final class WpEventMailSettingsProvider implements EventMailSettingsProvider
{
    public function get(EventId $eventId): EventMailSettings
    {
        return new EventMailSettings(
            sendAdminMailsToResponsible: $this->getBool($eventId, EventMeta::MAIL_TO_RESPONSIBLE),
            responsibleAsReplyTo: $this->getBool($eventId, EventMeta::RESPONSIBLE_AS_REPLY),
        );
    }

    private function getBool(EventId $eventId, string $metaKey): bool
    {
        return filter_var(
            get_post_meta($eventId->toInt(), $metaKey, true),
            FILTER_VALIDATE_BOOL,
        );
    }
}
