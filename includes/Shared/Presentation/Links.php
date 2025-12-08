<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Presentation;

final class Links
{
    public const NS = 'events/v3';
    public const PATTERNS = [
        'events'    => '/events/%d',
        'location' => '/location/%d',
        'person'  => '/person/%d',
        'attachment' => '/attachment/%d',
    ];

    public static function restRoute(string $type, string $regex): array
    {
        if (!isset(self::PATTERNS[$type])) {
            throw new \InvalidArgumentException("Unknown type '$type'");
        }
        return [
            "ns" => self::NS,
            "path" => str_replace('%d', $regex, self::PATTERNS[$type]),
        ];
    }

    public static function iri(string $type, int $id): string
    {
        $pattern = self::PATTERNS[$type] ?? throw new \InvalidArgumentException("Unknown type '$type'");
        return rest_url(self::NS . sprintf($pattern, $id));
    }

    public static function friendly(int $id): ?string
    {

        $url = get_permalink($id, false);
        return is_string($url) && $url !== '' ? $url : null;
    }
}
