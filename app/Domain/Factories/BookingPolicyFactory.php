<?php

namespace Contexis\Events\Domain\Factories;

use Contexis\Events\Domain\ValueObjects\BookingPolicy;

final class BookingPolicyFactory {
	public static function create(array $data): BookingPolicy {
		if (empty($data['enabled'])) {
			return new BookingPolicy(
				enabled: false,
				start: null,
				end: null,
				defaultStart: new \DateTimeImmutable(),
				defaultEnd: new \DateTimeImmutable(),
				capacity: 0
			);
		}

		return new BookingPolicy(
			enabled: true,
			start: isset($data['start']) ? new \DateTimeImmutable($data['start']) : null,
			end: isset($data['end']) ? new \DateTimeImmutable($data['end']) : null,
			defaultStart: new \DateTimeImmutable($data['default_start']),
			defaultEnd: new \DateTimeImmutable($data['default_end']),
			capacity: (int) ($data['capacity'] ?? 0)
		);
	}
}