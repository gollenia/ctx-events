<?php

namespace Contexis\Events\Person\Presentation;

use Contexis\Events\Person\Application\PersonDto;
use Contexis\Events\Shared\Presentation\Links;

final class PersonResource implements \JsonSerializable
{
    public function __construct(
        private readonly PersonDto $personDto
    ) {
    }

    private function getJsonLd(): array
    {
        $jsonLd = [
            "@context" => "https://schema.org/Person",
            "@type" => "Person",
            "@id" => Links::iri('person', $this->personDto->id)
        ];

        return $jsonLd;
    }

    public function jsonSerialize(): mixed
    {

        return [
            ...$this->getJsonLd(),
            'id' => $this->personDto->id,
            'givenName' => $this->personDto->givenName,
            'familyName' => $this->personDto->familyName,
            'email' => $this->personDto->email->address(),
            'telephone' => $this->personDto->telephone,
            'sameAs' => $this->personDto->sameAs,
        ];
    }
}
