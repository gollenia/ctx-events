<?php

namespace Contexis\Events\Event\Presentation;

use Contexis\Events\Event\Application\EventDto;
use Contexis\Events\Location\Presentation\LocationResource;
use Contexis\Events\Media\Presentation\ImageResource;
use Contexis\Events\Shared\Presentation\Links;
use JsonSerializable;

class EventResource implements JsonSerializable
{
    public function __construct(
        public readonly EventDto $event_dto,
    ) {
    }

    private function getJsonLd(): array
    {
        $jsonLd = [
            "@context" => "https://schema.org/Event",
            "@type" => "Event",
            "@id" => Links::iri('event', $this->event_dto->id)
        ];

        return $jsonLd;
    }

    public function jsonSerialize(): array
    {
        $result = [
            ...$this->getJsonLd(),
            'id' => $this->event_dto->id,
            'link' => Links::friendly($this->event_dto->id),
            'name' => $this->event_dto->name,
            'description' => $this->event_dto->description,
            'status' => $this->event_dto->status->value,
            'startDate' => $this->event_dto->startDate->format('c'),
            'endDate' => $this->event_dto->endDate?->format('c'),
            'audience' => $this->event_dto->audience
        ];

        $includes = [];

        if ($this->event_dto->locationDto) {
            $includes['location'] = new LocationResource($this->event_dto->locationDto);
        }

        if ($this->event_dto->imageDto) {
            $includes['image'] = new ImageResource($this->event_dto->imageDto);
        }

        if (!empty($includes)) {
            $result['includes'] = $includes;
        }

        return $result;
    }
}
