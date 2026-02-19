<?php
declare(strict_types=1);

namespace Contexis\Events\Media\Domain;

final readonly class ImageSize
{
    public function __construct(
        public string $url,
        public int $width,
        public int $height
    ) {
    }
}
