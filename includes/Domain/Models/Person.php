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
        public readonly ?string $honorificSuffix,
        public readonly ?string $honorificPrefix,
        public readonly Email $email,
        public readonly ?string $telephone,
        public readonly ?string $website,
        public readonly ?string $jobTitle,
        public readonly ?string $worksFor,
        public readonly ImageId $attachment_id
    ) {
    }
}
