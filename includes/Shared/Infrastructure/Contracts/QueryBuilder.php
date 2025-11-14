<?php

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface QueryBuilder
{
    public static function build(QueryRequest $query): array;
}
