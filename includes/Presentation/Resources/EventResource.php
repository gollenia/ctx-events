<?php

namespace Contexis\Events\Presentation\Resources;

use Contexis\Events\Application\DTO as DTO;
use Contexis\Events\Presentation\Services\Links;
use JsonSerializable;

class EventResource implements JsonSerializable
{
    public function __construct(
        public readonly DTO\Event $event,
    ) {
    }

    private function getJsonLd(): array
    {
        $jsonLd = [
            "@context" => "https://schema.org/Event",
            "@type" => "Event",
            "@id" => Links::iri('event', $this->event->id)
        ];

        return $jsonLd;
    }

    public function jsonSerialize(): array
    {
        $result = [
            ...$this->getJsonLd(),
            'id' => $this->event->id,
            'link' => Links::friendly($this->event->id),
            'name' => $this->event->name,
            'description' => $this->event->description,
            'status' => $this->event->eventStatus->value,
            'startDate' => $this->event->startDate->format('c'),
            'endDate' => $this->event->endDate?->format('c'),
            'bookingPolicy' => $this->event->bookingPolicy,
            'audience' => $this->event->audience
        ];

        $includes = [];

        if ($this->event->includes && $this->event->includes->location) {
            $includes['location'] = new LocationResource($this->event->includes->location);
        }

        if ($this->event->includes && $this->event->includes->image) {
            $includes['image'] = new AttachmentResource($this->event->includes->image);
        }

        if (!empty($includes)) {
            $result['includes'] = $includes;
        }

        return $result;
    }
}
