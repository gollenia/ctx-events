<?php

declare(strict_types=1);

use Contexis\Events\Payment\Application\Dtos\CouponListItem;
use Contexis\Events\Payment\Domain\CouponId;
use Contexis\Events\Payment\Domain\DiscountType;
use Contexis\Events\Payment\Presentation\Resources\CouponListItemResource;
use Contexis\Events\Shared\Domain\ValueObjects\Status;

function makeCouponListItem(
    ?int $usageLimit,
    ?int $usageCount,
): CouponListItem {
    return new CouponListItem(
        id: CouponId::from(1),
        title: 'Coupon',
        code: 'TEST',
        discountType: DiscountType::PERCENTAGE,
        discountValue: 10,
        validFrom: null,
        expiresAt: null,
        usageLimit: $usageLimit,
        usageCount: $usageCount,
        isGlobal: false,
        status: Status::Published,
    );
}

test('coupon list item is used up when usage count reaches limit', function () {
    expect(makeCouponListItem(5, 5)->isUsedUp())->toBeTrue()
        ->and(makeCouponListItem(5, 7)->isUsedUp())->toBeTrue();
});

test('coupon list item is not used up when limit is not reached or missing', function () {
    expect(makeCouponListItem(5, 4)->isUsedUp())->toBeFalse()
        ->and(makeCouponListItem(5, null)->isUsedUp())->toBeFalse()
        ->and(makeCouponListItem(null, 100)->isUsedUp())->toBeFalse();
});

test('coupon list resource exposes used up status for published used up coupons', function () {
    $resource = CouponListItemResource::fromDTO(makeCouponListItem(5, 5));

    expect($resource->status)->toBe('used_up');
});
