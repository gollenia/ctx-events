<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Presentation\Resources;

use Contexis\Events\Event\Application\DTOs\OccurrenceResponse;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Contexis\Events\Shared\Presentation\RestRoute;

/**
 * Opt-in representation for a concrete event occurrence.
 *
 * The visible event fields deliberately mirror EventResource. `id` is an
 * opaque occurrence identity in this representation; consumers that need a
 * post URL use the nullable `eventId` instead.
 */
final readonly class OccurrenceResource implements Resource
{
    private function __construct(
        public string $id,
        public string $type,
        public ?int $eventId,
        public ?int $seriesId,
        public ?string $url,
        public string $name,
        public ?string $description,
        public string $status,
        public string $startDate,
        public string $endDate,
        public ?string $audience,
        /** @var array{occurrenceId: string, type: 'virtual'|'detached', scheduledStartsAt: string, seriesId: int}|null */
        public ?array $recurrence,
    ) {
    }

    public static function fromDto(OccurrenceResponse $occurrence, RestRoute $route): self
    {
        $recurrence = $occurrence->type === 'real'
            ? null
            : [
                'occurrenceId' => $occurrence->occurrenceId,
                'type' => $occurrence->type,
                'scheduledStartsAt' => $occurrence->scheduledStartsAt?->format(DATE_ATOM),
                'seriesId' => $occurrence->seriesId,
            ];

        return new self(
            id: $occurrence->occurrenceId,
            type: $occurrence->type,
            eventId: $occurrence->eventId,
            seriesId: $occurrence->seriesId,
            url: self::url($occurrence, $route),
            name: $occurrence->name,
            description: $occurrence->description,
            status: $occurrence->status->value,
            startDate: $occurrence->startDate->format(DATE_ATOM),
            endDate: $occurrence->endDate->format(DATE_ATOM),
            audience: $occurrence->audience,
            recurrence: $recurrence,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'eventId' => $this->eventId,
            'seriesId' => $this->seriesId,
            'url' => $this->url,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'audience' => $this->audience,
            'bookingSummary' => null,
            'includes' => null,
            'schema' => null,
            'recurrence' => $this->recurrence,
        ];
    }

    private static function url(OccurrenceResponse $occurrence, RestRoute $route): ?string
    {
        if ($occurrence->eventId !== null) {
            return $route->getFriendlyUrl($occurrence->eventId);
        }

        if ($occurrence->seriesId === null) {
            return null;
        }

        $url = get_permalink($occurrence->seriesId);
        if (!is_string($url) || $url === '') {
            return null;
        }

        return add_query_arg('occurrence', $occurrence->occurrenceId, $url);
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
