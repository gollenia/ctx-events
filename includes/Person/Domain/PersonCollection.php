<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Domain;

use Contexis\Events\Person\Domain\Person;
use Contexis\Events\Shared\Domain\Abstract\Collection;

readonly class PersonCollection extends Collection
{
    public function __construct(Person ...$persons)
    {
        $this->items = $persons;
    }
}
