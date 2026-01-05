<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

class BlockAttributesResolver
{
    private static array $cache = [];

    public static function getDefaults(string $blockName): array
    {
        if (isset(self::$cache[$blockName])) {
            return self::$cache[$blockName];
        }

        $registry = \WP_Block_Type_Registry::get_instance();
        $blockType = $registry->get_registered($blockName);

        if (!$blockType) return [];

        $defaults = [];
        foreach ($blockType->attributes as $key => $attribute) {
            if (is_array($attribute) && array_key_exists('default', $attribute)) {
                $defaults[$key] = $attribute['default'];
            }
        }

        self::$cache[$blockName] = $defaults;
        return $defaults;
    }
}