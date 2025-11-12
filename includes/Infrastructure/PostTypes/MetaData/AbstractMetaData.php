<?php 

namespace Contexis\Events\Infrastructure\PostTypes\MetaData;

abstract class AbstractMetaData {

	protected static array $metadata = [];

	public static function baseArgs(): array
    {
        return [
            'single'            => true,
            'auth_callback'     => static function (): bool {
                return current_user_can('edit_posts');
            }
        ];
    }

	public static function getRegisterArgs(): array
    {
        $base = self::baseArgs();
        $out  = [];

        foreach (self::$metadata as $name => $args) {
            $out[$name] = array_merge(
                $base,
                $args
            );

            if (!isset($out[$name]['show_in_rest'])) {
                $out[$name]['show_in_rest'] = true;
            }
        }

        return $out;
    }

	public static function registerAll(string $post_type): void
    {
        foreach (self::getRegisterArgs() as $name => $args) {
			register_post_meta($post_type, $name, $args);
        }
    }
}