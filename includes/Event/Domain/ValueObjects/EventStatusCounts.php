<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\ValueObjects\StatusCounts;

class EventStatusCounts extends StatusCounts
{
	public function __construct(
		int $publish = 0,
		int $future = 0,
		int $draft = 0,
		int $private = 0,
		int $pending = 0,
		int $trash = 0,
		public int $cancelled = 0,
	) {
		parent::__construct(
			publish: $publish,
			future: $future,
			draft: $draft,
			private: $private,
			pending: $pending,
			trash: $trash,
		);
	}

	public static function fromArray(array $data): static
	{
		return new static(
			publish: (int) ($data['publish'] ?? 0),
			future: (int) ($data['future'] ?? 0),
			draft: (int) ($data['draft'] ?? 0),
			private: (int) ($data['private'] ?? 0),
			pending: (int) ($data['pending'] ?? 0),
			trash: (int) ($data['trash'] ?? 0),
			cancelled: (int) ($data['cancelled'] ?? 0),
		);
	}

	public function toArray(): array
	{
		return [
			...parent::toArray(),
			'cancelled' => $this->cancelled,
		];
	}
}
