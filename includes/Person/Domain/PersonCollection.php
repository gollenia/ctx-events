<?php

namespace Contexis\Events\Person\Domain;

use Contexis\Events\Domain\Models\Person;
use Contexis\Events\Shared\Domain\Abstract\Collection;

class PersonCollection extends Collection
{
    public function __construct(...$contacts)
    {
        $this->items = $contacts;
    }
}
