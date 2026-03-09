<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

final class EventCoupons
{
	/** @param int[] $allowedIds WP post IDs of event-specific coupons */
	public function __construct(
		public readonly bool $enabled,
		public readonly array $allowedIds = [],
	) {
	}

	public function maybeApplicable(): bool
	{
		return $this->enabled;
	}

	public function isAllowed(\Contexis\Events\Payment\Domain\CouponId $couponId): bool
	{
		return in_array($couponId->toInt(), $this->allowedIds, strict: true);
	}
}