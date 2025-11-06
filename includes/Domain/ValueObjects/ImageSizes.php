<?php

namespace Contexis\Events\Domain\ValueObjects;

final readonly class ImageSizes
{
    public function __construct(
        public ?string $thumbnail,
        public ?string $medium,
        public ?string $large,
        public ?string $original
    ) {
    }

    public function toArray(): array
    {
        return [
            'thumbnail' => $this->thumbnail,
            'medium'    => $this->medium,
            'large'     => $this->large,
            'original'  => $this->original,
        ];
    }
}
