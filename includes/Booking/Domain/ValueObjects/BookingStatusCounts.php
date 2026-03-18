<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\Contracts\StatusCountsInterface;

final readonly class BookingStatusCounts implements StatusCountsInterface
{
	public function __construct(
		public int $pending = 0,
		public int $approved = 0,
		public int $canceled = 0,
		public int $expired = 0,
	) {
	}

	/**
	 * @param array<string, int> $data
	 */
	public static function fromArray(array $data): static
	{
		return new static(
			pending: (int) ($data['pending'] ?? 0),
			approved: (int) ($data['approved'] ?? 0),
			canceled: (int) ($data['canceled'] ?? 0),
			expired: (int) ($data['expired'] ?? 0),
		);
	}

	/**
	 * @return array<string, int>
	 */
	public function toArray(): array
	{
		return [
			'pending' => $this->pending,
			'approved' => $this->approved,
			'canceled' => $this->canceled,
			'expired' => $this->expired,
		];
	}
}
