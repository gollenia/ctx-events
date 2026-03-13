<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\ValueObjects;

final readonly class StatusCounts
{
	public function __construct(
		public int $publish = 0,
		public int $future = 0,
		public int $draft = 0,
		public int $private = 0,
		public int $pending = 0,
		public int $trash = 0,
	) {
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
		);
	}

	public function toArray(): array
	{
		return [
			'publish' => $this->publish,
			'future' => $this->future,
			'draft' => $this->draft,
			'private' => $this->private,
			'pending' => $this->pending,
			'trash' => $this->trash,
		];
	}
}
