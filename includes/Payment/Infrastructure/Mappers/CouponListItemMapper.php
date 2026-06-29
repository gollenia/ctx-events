<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Mappers;

use Contexis\Events\Payment\Application\Dtos\CouponListItem;
use Contexis\Events\Payment\Domain\CouponId;
use Contexis\Events\Payment\Domain\DiscountType;
use Contexis\Events\Payment\Infrastructure\CouponMeta;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

final class CouponListItemMapper
{
    public static function map(PostSnapshot $post): CouponListItem
    {
        return new CouponListItem(
            id: CouponId::from($post->id),
            title: $post->post_title,
            code: $post->getString(CouponMeta::CODE, ''),
            discountType: DiscountType::fromString($post->getString(CouponMeta::TYPE, 'percent')),
            discountValue: $post->getInt(CouponMeta::VALUE, 0) ?? 0,
            validFrom: $post->getDateTime(CouponMeta::VALID_FROM),
            expiresAt: $post->getDateTime(CouponMeta::EXPIRES_AT),
            usageLimit: $post->getInt(CouponMeta::LIMIT),
            usageCount: $post->getInt(CouponMeta::USED),
            isGlobal: $post->getBool(CouponMeta::GLOBAL) ?? false,
            status: Status::from($post->post_status),
        );
    }
}
