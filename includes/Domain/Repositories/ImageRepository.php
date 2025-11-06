<?php

namespace Contexis\Events\Domain\Repositories;

use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\Image;

interface ImageRepository
{
    public function find(?ImageId $id): ?Image;
}
