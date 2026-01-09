<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface DatabaseMapper
{
	public static function map(array $data): object;
}