<?php

namespace Contexis\Events\Domain\Models;

final class Record {

	public function __construct(
		public string $text,
		public int $user_id,
		public \DateTimeImmutable $created_at
	) {}
}