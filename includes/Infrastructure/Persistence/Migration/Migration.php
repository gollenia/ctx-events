<?php

namespace Contexis\Events\Infrastructure\Persistence\Migration;

interface Migration {
	public function get_table_name(): string;
	public function get_columns(): array;
	public function get_columns_as_string(): string;
}