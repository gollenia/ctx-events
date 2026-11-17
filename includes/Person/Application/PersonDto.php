<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Application;

use Contexis\Events\Media\Application\ImageDto;
use Contexis\Events\Person\Domain\Person;
use Contexis\Events\Shared\Domain\ValueObjects\Email;

final class PersonDto
{
	/** @param array<mixed> $sameAs */
    public function __construct(
        public readonly int $id,
        public readonly ?string $givenName,
        public readonly ?string $familyName,
        public readonly ?string $honorificSuffix,
        public readonly ?string $honorificPrefix,
        public readonly ?Email $email,
        public readonly ?string $telephone,
        public readonly ?array $sameAs,
        public readonly ?string $jobTitle,
        public readonly ?string $worksFor,
        public readonly ?ImageDto $image = null,
    ) {
    }

    public static function fromDomainModel(Person $person, ?ImageDto $image = null): self
    {
        return new self(
            id: $person->id->toInt(),
            givenName: $person->givenName,
            familyName: $person->familyName,
            honorificSuffix: $person->honorificSuffix,
            honorificPrefix: $person->honorificPrefix,
            email: $person->email,
            telephone: $person->telephone,
            sameAs: $person->sameAs,
            jobTitle: $person->jobTitle,
            worksFor: $person->worksFor,
            image: $image,
        );
    }
}
