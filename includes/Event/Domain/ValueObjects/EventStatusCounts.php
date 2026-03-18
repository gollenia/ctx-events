<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\Contracts\StatusCountsInterface;

final readonly class EventStatusCounts implements StatusCountsInterface
{
	public function __construct(
		public int $publish = 0,
		public int $future = 0,
		public int $draft = 0,
		public int $private = 0,
		public int $pending = 0,
		public int $trash = 0,
		public int $cancelled = 0,
	) {

	}

	/**
	 * @param array<mixed> $data
	 */
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

	/**
	 * @return array<string, int>
	 */
	public function toArray(): array
	{
		return [
			'publish' => $this->publish,
			'future' => $this->future,
			'draft' => $this->draft,
			'private' => $this->private,
			'pending' => $this->pending,
			'trash' => $this->trash,
			'cancelled' => $this->cancelled,
		];
	}
}
