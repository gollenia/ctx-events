<?php

namespace Contexis\Events\Interfaces;

interface Model {
	public static function get_by_id(int $id): ?self;
	public function get_rest_fields(): array;
}