<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Payment\Domain\Coupon;
use Contexis\Events\Payment\Domain\CouponCode;
use Contexis\Events\Payment\Domain\CouponCollection;
use Contexis\Events\Payment\Domain\CouponId;
use Contexis\Events\Payment\Domain\DiscountType;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Infrastructure\Contracts\PostMapper;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

class CouponMapper implements PostMapper
{
	public static function map(PostSnapshot $post): Coupon
	{
		$code = $post->getString(CouponMeta::CODE) ?? $post->getString('_coupon_code', '');
		$type = $post->getString(CouponMeta::TYPE) ?? $post->getString('_coupon_type', 'fixed');
		$value = $post->getInt(CouponMeta::VALUE) ?? $post->getInt('_coupon_value', 0);
		$validFrom = $post->getDateTime(CouponMeta::VALID_FROM) ?? $post->getDateTime('_coupon_valid_from');
		$expiresAt = $post->getDateTime(CouponMeta::EXPIRES_AT) ?? $post->getDateTime('_coupon_expiry');
		$usageLimit = $post->getInt(CouponMeta::LIMIT) ?? $post->getInt('_coupon_limit');
		$usageCount = $post->getInt(CouponMeta::USED) ?? $post->getInt('_coupon_used');

		return new Coupon(
			id: new CouponId($post->id),
			code: CouponCode::fromString($code),
			name: $post->post_title,
			discountType: DiscountType::fromString($type),
			value: $value,
			validFrom: $validFrom,
			expiresAt: $expiresAt,
			usageLimit: $usageLimit,
			usageCount: $usageCount,
			description: $post->post_content,
			status: Status::from($post->post_status),
			isGlobal: $post->getBool(CouponMeta::GLOBAL) ?? false,
		);
	}

	public static function mapCollection(array $posts): CouponCollection
	{
		return CouponCollection::from(...array_map(fn(PostSnapshot $post) => self::map($post), $posts));	
	}
}
