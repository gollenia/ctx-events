<?php

namespace Contexis\Events\Infrastructure\Persistence\Query;

use Contexis\Events\Core\Contracts\Criteria;
use Contexis\Events\Core\Contracts\QueryOptions;

abstract class AbstractWpQuery implements QueryOptions
{
    protected array $query = [];
    abstract public function build(Criteria $criteria): QueryOptions;

    public function addArgs(array $args): static
    {
        $this->query = array_merge($this->query, $args);
        return $this;
    }

    public function addArg(string $key, mixed $value): static
    {
        $this->query[$key] = $value;
        return $this;
    }

    public function toArray(): array
    {
        return $this->query;
    }
}
