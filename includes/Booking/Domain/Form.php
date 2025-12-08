<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

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
