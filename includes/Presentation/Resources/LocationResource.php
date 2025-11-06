<?php

namespace Contexis\Events\Presentation\Resources;

use Contexis\Events\Application\DTO as DTO;
use Contexis\Events\Presentation\Services\Links;
use JsonSerializable;

class LocationResource implements JsonSerializable
{
    public function __construct(
        public readonly DTO\Location $location,
    ) {
    }

    private function getJsonLd(): array
    {
        $jsonLd = [
           "@context" => "https://schema.org/Place",
           "@type" => "Place",
           "@id" => Links::iri('location', $this->location->id)
        ];

        return $jsonLd;
    }

    public function jsonSerialize(): array
    {
        return [
            ...$this->getJsonLd(),
            'link' => Links::friendly($this->location->id),
            'id' => $this->location->id,
            'name' => $this->location->name,
            'address' => $this->location->address
        ];
    }
}
