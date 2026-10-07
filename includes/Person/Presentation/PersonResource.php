<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Presentation;

use Contexis\Events\Person\Application\PersonDto;
use Contexis\Events\Shared\Presentation\Links;

final class PersonResource implements \JsonSerializable
{
    public function __construct(
        private readonly PersonDto $personDto
    ) {
    }

	/**
	 * @return array<string, mixed>
	 */
    private function getJsonLd(): array
    {
        $jsonLd = [
            "@context" => "https://schema.org/Person",
            "@type" => "Person"
        ];

        return $jsonLd;
    }

    public function jsonSerialize(): mixed
    {

        return [
            ...$this->getJsonLd(),
            'id' => $this->personDto->id,
			'honorificPrefix' => $this->personDto->honorificPrefix,
            'givenName' => $this->personDto->givenName,
            'familyName' => $this->personDto->familyName,
			'honorificSuffix' => $this->personDto->honorificSuffix,
			'email' => $this->personDto->email?->address(),
            'telephone' => $this->personDto->telephone,
            'sameAs' => $this->personDto->sameAs,
        ];
    }
}
