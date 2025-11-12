<?php

namespace Contexis\Events\Core\Contracts;

interface QueryBuilder
{
    public static function build(QueryRequest $query): array;
}
