<?php

namespace Contexis\Events\Core\Contracts;

use Contexis\Events\Core\Contracts\Criteria;

interface QueryOptions
{
    public function build(Criteria $criteria): QueryOptions;
    public function toArray(): array;
}
