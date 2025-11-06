<?php

namespace Tests\Support;

use Contexis\Events\Domain\Repositories\ImageRepository;
use Contexis\Events\Domain\ValueObjects\Image;
use Contexis\Events\Domain\Collections\ImageCollection;
use Contexis\Events\Core\Contracts\QueryOptions;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;

final class FakeImageRepository implements ImageRepository
{
    public ?Image $toReturn;
    public ?ImageId $lastFindArg = null;

    public function __construct(?Image $toReturn = null)
    {
        $this->toReturn = $toReturn;
    }

    public function find(?ImageId $id): ?Image
    {
        $this->lastFindArg = $id;
        return $this->toReturn;
    }

    /**
     * Interface: public function query(QueryOptions $args): static
     */
    public function query(QueryOptions $args): static
    {
        return $this;
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
