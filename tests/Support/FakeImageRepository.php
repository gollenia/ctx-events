<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Media\Domain\ImageRepository;
use Contexis\Events\Media\Domain\Image;
use Contexis\Events\Media\Domain\ImageCollection;
use Contexis\Events\Media\Domain\ImageId;

final class FakeImageRepository implements ImageRepository
{
    public ?Image $toReturn;

    public function __construct(?Image $toReturn = null)
    {
        $this->toReturn = $toReturn;
    }

    public function find(?ImageId $id): ?Image
    {
        return $this->toReturn;
    }

    public function findByIds(array $ids): ImageCollection
    {
        return new ImageCollection();
    }

    /**
     * Interface: public function first(): ?Image
     */
    public function first(): ?Image
    {
        return $this->toReturn;
    }
    /**
     * Interface: public function count(): int
     */
    public function count(): int
    {
        return $this->toReturn ? 1 : 0;
    }
}
