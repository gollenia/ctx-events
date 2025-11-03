<?php

namespace Contexis\Events\Domain\ValueObjects;

final class LogEntry {

	public function __construct(
		public string $text,
		public int $user_id,
		public \DateTimeImmutable $created_at
	) {}
}