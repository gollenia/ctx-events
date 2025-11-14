<?php

namespace Contexis\Events\Person\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Abstracts\MetaData;

final class PersonMeta extends MetaData
{
    public const FIRST_NAME = '_person_first_name';
    public const LAST_NAME  = '_person_last_name';
    public const EMAIL      = '_person_email';
    public const PHONE      = '_person_phone';
    public const GENDER     = '_person_gender';
    public const PREFIX     = '_person_prefix';
    public const SUFFIX     = '_person_suffix';
    public const POSITION   = '_person_position';
    public const ORGANIZATION = '_person_organization';
    public const WEBSITE    = '_person_website';
    public const SAME_AS    = '_person_same_as';

    protected static array $metadata = [
        self::FIRST_NAME => ['type' => 'string'],
        self::LAST_NAME  => ['type' => 'string'],
        self::EMAIL      => [
            'type' => 'string',
            'show_in_rest' => [
                'schema' => ['format' => 'email'],
            ],
        ],
        self::PHONE      => ['type' => 'string'],
        self::GENDER     => ['type' => 'string'],
        self::PREFIX     => ['type' => 'string'],
        self::SUFFIX     => ['type' => 'string'],
        self::POSITION   => ['type' => 'string'],
        self::ORGANIZATION => ['type' => 'string'],
        self::WEBSITE    => ['type' => 'string'],
        self::SAME_AS    => [
            'type' => 'array',
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
        ],
    ];
}
