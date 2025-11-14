<?php

namespace Contexis\Events\Pwerson\Application;

use Contexis\Events\Domain\ValueObjects\Email;

abstract class PersonDto
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $givenName,
        public readonly ?string $familyName,
        public readonly ?string $honorificSuffix,
        public readonly ?string $honorificPrefix,
        public readonly Email $email,
        public readonly ?string $telephone,
        public readonly ?string $website,
        public readonly ?string $jobTitle,
        public readonly ?string $worksFor
    ) {
    }

    public static function fromDomainModel(\Contexis\Events\Domain\Models\Person $person): self
    {
        return new self(
            id: $person->id->toInt(),
            givenName: $person->givenName,
            familyName: $person->familyName,
            honorificSuffix: $person->honorificSuffix,
            honorificPrefix: $person->honorificPrefix,
            email: new Email($person->email),
            telephone: $person->telephone,
            website: $person->website,
            jobTitle: $person->jobTitle,
            worksFor: $person->worksFor
        );
    }
}
