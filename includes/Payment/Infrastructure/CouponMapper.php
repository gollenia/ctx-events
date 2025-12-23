<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Payment\Domain\Coupon;
use Contexis\Events\Payment\Domain\CouponCode;
use Contexis\Events\Payment\Domain\CouponId;
use Contexis\Events\Payment\Domain\DiscountType;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Infrastructure\Contracts\DatabaseMapper;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

class CouponMapper implements DatabaseMapper
{
	public static function map(PostSnapshot $post): Coupon
	{
		return new Coupon(
			id: new CouponId($post->id),
			code: CouponCode::fromString($post->getString(CouponMeta::CODE)),
			name: $post->post_title,
			discountType: DiscountType::fromString($post->getString(CouponMeta::TYPE)),
			value: $post->getInt(CouponMeta::VALUE),
			validFrom: $post->getDateTime(CouponMeta::VALID_FROM),
			expiresAt: $post->getDateTime(CouponMeta::EXPIRES_AT),
			usageLimit: $post->getInt(CouponMeta::LIMIT),
			usageCount: $post->getInt(CouponMeta::USED),
			description: $post->post_content,
			status: Status::from($post->post_status)
		);
	}
}