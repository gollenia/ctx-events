<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface DatabaseMapper
{
	/**
	 * @param array<string, mixed> $data
	 * @return object
	 */
	public static function map(array $data): object;
}