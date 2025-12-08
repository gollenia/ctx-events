<?php
declare(strict_types=1);

namespace Contexis\Events\Media\Application;

use Contexis\Events\Media\Domain\ImageCollection;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Shared\Domain\Abstract\DtoCollection;

final class ImageDtoCollection extends DtoCollection
{
    public function __construct(
        ImageDto ...$images
    ) {
        $this->items = $images;
    }

    public static function fromDomainCollection(ImageCollection $collection): ImageDtoCollection
    {
        $items = [];
        foreach ($collection as $item) {
            $items[] = ImageDto::fromDomainModel($item);
        }
        return new ImageDtoCollection(...$items);
    }

    public function findById(ImageId $id): ?ImageDto
    {
        foreach ($this->items as $imageDto) {
            if ($imageDto->id === $id->toInt()) {
                return $imageDto;
            }
        }
        return null;
    }
}
