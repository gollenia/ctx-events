<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Application;

final class PersonIncludes
{
    public function __construct(
        public readonly bool $image = false
    ) {
    }

    public static function fromArray(array $includes): self
    {
        return new self(
            in_array('image', $includes, true)
        );
    }
}
