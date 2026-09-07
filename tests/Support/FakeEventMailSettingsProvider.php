<?php

declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Communication\Application\Contracts\EventMailSettingsProvider;
use Contexis\Events\Communication\Domain\ValueObjects\EventMailSettings;
use Contexis\Events\Event\Domain\ValueObjects\EventId;

final class FakeEventMailSettingsProvider implements EventMailSettingsProvider
{
    /** @param array<int, EventMailSettings> $settingsByEventId */
    public function __construct(private array $settingsByEventId = [])
    {
    }

    public function get(EventId $eventId): EventMailSettings
    {
        return $this->settingsByEventId[$eventId->toInt()] ?? new EventMailSettings();
    }
}
