<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Domain;

use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\Traits\HasStatus;
use Contexis\Events\Shared\Domain\ValueObjects\Email;

final class Person
{
    use HasStatus;

    public function __construct(
        public readonly PersonId $id,
        public readonly Status $status,
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

	public function getStatus(): Status
	{
		return $this->status;
	}

	public function setStatus(Status $status): static
	{
		return clone($this, ['status' => $status]);
	}
}
