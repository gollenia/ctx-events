<?php
declare(strict_types=1);

namespace Contexis\Events\Media\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final class ImageCollection extends Collection
{
    public function __construct(
        Image ...$images
    ) {
        $this->items = $images;
    }
}
