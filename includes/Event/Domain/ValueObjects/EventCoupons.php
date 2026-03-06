<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

final class EventCoupons
{
	public function __construct(
		public readonly bool $enabled,
	) {
	}

	public function maybeApplicable(): bool
	{
		return $this->enabled;
	}
}