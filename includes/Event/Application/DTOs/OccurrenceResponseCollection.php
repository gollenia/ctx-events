<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

use Contexis\Events\Shared\Domain\Abstract\DtoCollection;

/** @extends DtoCollection<OccurrenceResponse> */
final readonly class OccurrenceResponseCollection extends DtoCollection
{
    public static function from(OccurrenceResponse ...$items): self
    {
        return new self($items);
    }
}
