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
		$code = $post->getString(CouponMeta::CODE, '');
		$type = $post->getString(CouponMeta::TYPE, 'percent');
		$value = $post->getInt(CouponMeta::VALUE, 0);
		$validFrom = $post->getDateTime(CouponMeta::VALID_FROM);
		$expiresAt = $post->getDateTime(CouponMeta::EXPIRES_AT);
		$usageLimit = $post->getInt(CouponMeta::LIMIT);
		$usageCount = $post->getInt(CouponMeta::USED);
		$description = $post->getString(CouponMeta::DESCRIPTION, '');

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
			description: $description,
			status: Status::from($post->post_status),
			isGlobal: $post->getBool(CouponMeta::GLOBAL) ?? false,
		);
	}

	public static function mapCollection(array $posts): CouponCollection
	{
		return CouponCollection::from(...array_map(fn(PostSnapshot $post) => self::map($post), $posts));	
	}
}
