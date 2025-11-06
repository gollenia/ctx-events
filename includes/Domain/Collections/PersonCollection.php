<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Models\Person;

class PersonCollection extends AbstractCollection
{
    public function __construct(...$contacts)
    {
        $this->items = $contacts;
    }
}
