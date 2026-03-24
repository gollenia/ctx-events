<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Abstracts\MetaData;

final class CouponMeta extends MetaData
{
    public const CODE        = '_code';
    public const TYPE        = '_type';
    public const VALUE       = '_value';
    public const EXPIRES_AT  = '_expires_at';
    public const VALID_FROM  = '_valid_from';
    public const LIMIT       = '_limit';
    public const USED        = '_used';
    public const STATUS      = '_status';
    public const GLOBAL      = '_is_global';

    protected static array $metadata = [
        self::CODE   => ['type' => 'string'],
        self::TYPE   => ['type' => 'string', 'default' => 'percent'],
        self::VALUE  => ['type' => 'number', 'default' => 0],
        self::EXPIRES_AT => ['type' => 'string'],
		self::VALID_FROM => ['type' => 'string'],
        self::LIMIT  => ['type' => 'number'],
        self::USED   => ['type' => 'number'],
        self::STATUS => ['type' => 'string'],
        self::GLOBAL => ['type' => 'boolean', 'default' => false],
    ];
}
