<?php 

namespace Contexis\Events\Infrastructure\PostTypes\MetaData;

final class CouponMeta extends AbstractMetaData {

	public const CODE        = '_coupon_code';
	public const TYPE        = '_coupon_type';
	public const VALUE       = '_coupon_value';
	public const EXPIRY      = '_coupon_expiry';
	public const LIMIT       = '_coupon_limit';
	public const USED        = '_coupon_used';
	public const STATUS      = '_coupon_status';
	public const GLOBAL      = '_coupon_global';

	protected static array $metadata = [
		self::CODE   => ['type' => 'string'],
		self::TYPE   => ['type' => 'string'],
		self::VALUE  => ['type' => 'number'],
		self::EXPIRY => ['type' => 'string'],
		self::LIMIT  => ['type' => 'number'],
		self::USED   => ['type' => 'number'],
		self::STATUS => ['type' => 'string'],
		self::GLOBAL => ['type' => 'boolean'],
	];
}
