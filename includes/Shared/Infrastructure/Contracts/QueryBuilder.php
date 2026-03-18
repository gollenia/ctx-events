<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface QueryBuilder
{
	/**
	 * @return array<mixed>
	 */
    public static function build(QueryRequest $query): array;
}
