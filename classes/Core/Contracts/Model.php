<?php

namespace Contexis\Events\Core\Contracts;

interface Model {
	public static function get_by_id(int $id): ?self;
}