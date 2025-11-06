<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\Id\FormId;

class Form
{
    public function __construct(
        public readonly FormId $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly array $fields
    ) {
    }
}
