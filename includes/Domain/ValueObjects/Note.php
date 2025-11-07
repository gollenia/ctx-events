<?php

namespace Contexis\Events\Domain\ValueObjects;

final class AttendeeMeta
{
    public function __construct(
        public readonly string $key,
        public readonly string $value,
    ) {
    }
}
