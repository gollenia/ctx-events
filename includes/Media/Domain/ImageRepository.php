<?php
declare(strict_types=1);

namespace Contexis\Events\Media\Domain;

interface ImageRepository
{
    public function find(?ImageId $id): ?Image;
    public function findByIds(array $ids): ImageCollection;
}
