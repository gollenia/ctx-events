<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\DTOs;

use Contexis\Events\Shared\Domain\Abstract\DtoCollection;

final readonly class MailListResponse extends DtoCollection
{
    public static function from(MailListItem ...$items): self
    {
        return new self($items);
    }
}
