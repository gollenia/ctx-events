<?php

namespace Contexis\Events\Location\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Abstracts\MetaData;

final class LocationMeta extends MetaData
{
    public const ADDRESS   = '_location_address';
    public const CITY      = '_location_town';
    public const STATE     = '_location_state';
    public const POSTCODE  = '_location_postcode';
    public const REGION    = '_location_region';
    public const URL       = '_location_url';
    public const COUNTRY   = '_location_country';
    public const LATITUDE  = '_location_latitude';
    public const LONGITUDE = '_location_longitude';

    protected static array $metadata = [
        self::ADDRESS => ['type' => 'string'],
        self::CITY => ['type' => 'string'],
        self::STATE => ['type' => 'string'],
        self::POSTCODE => ['type' => 'string'],
        self::URL => ['type' => 'string'],
        self::COUNTRY => ['type' => 'string'],
        self::LATITUDE => ['type' => 'number'],
        self::LONGITUDE => ['type' => 'number']
    ];
}
