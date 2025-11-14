<?php

namespace Contexis\Events\Media\Domain;

interface ImageRepository
{
    public function find(?ImageId $id): ?Image;
}
