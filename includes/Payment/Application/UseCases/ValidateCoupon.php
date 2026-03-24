<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Application\Dtos\CouponCheckResponse;
use Contexis\Events\Payment\Domain\CouponRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class ValidateCoupon
{
    public function __construct(
        private CouponRepository $couponRepository,
        private EventRepository $eventRepository,
        private Clock $clock,
    ) {}

    public function execute(string $code, EventId $eventId, int $bookingPriceCents, string $currencyCode): CouponCheckResponse
    {
        $event = $this->eventRepository->find($eventId);

        if ($event === null) {
            throw new \DomainException('Event not found.');
        }

        if (!$event->allowsCoupons()) {
            throw new \DomainException('This event does not accept coupons.');
        }

        $coupon = $this->couponRepository->findByCode($code);

        if ($coupon === null) {
            throw new \DomainException("Coupon '{$code}' not found.");
        }

        if (!$coupon->isGlobal && !$event->eventCoupons->isAllowed($coupon->id)) {
            throw new \DomainException("Coupon '{$code}' is not valid for this event.");
        }

        if (!$coupon->isUsableAt($this->clock->now())) {
            throw new \DomainException("Coupon '{$code}' is not valid or has expired.");
        }

        $currency = Currency::fromCode($currencyCode);
        $discountAmount = $coupon->getDiscountAmount(Price::from($bookingPriceCents, $currency));

        return CouponCheckResponse::from(
            name: $coupon->name,
            discountType: $coupon->getDiscountType()->value,
            discountValue: $coupon->value,
            discountAmount: $discountAmount->amountCents,
        );
    }
}
