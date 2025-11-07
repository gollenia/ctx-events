<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\Id\PersonId;
use Contexis\Events\Domain\ValueObjects\Email;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\Media;

final class Person
{
    public function __construct(
        public readonly PersonId $id,
        public readonly ?string $givenName,
        public readonly ?string $familyName,
        public readonly ?string $honorificSuffix = null,
        public readonly ?string $honorificPrefix = null,
        public readonly ?Email $email = null,
        public readonly ?string $telephone = null,
        public readonly ?array $sameAs = null,
        public readonly ?string $jobTitle = null,
        public readonly ?string $worksFor = null,
        public readonly ?ImageId $imageId = null
    ) {
    }
}
