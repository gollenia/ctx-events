<?php
declare(strict_types=1);

namespace Contexis\Events\Media\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class ImageCollection extends Collection
{
    public static function from(Image ...$images): self
    {
        return new self($images);
    }
}
