<?php

namespace Contexis\Events\Core\Contracts;

interface Mapper
{
	public static function map(array $data): ?object;
}
