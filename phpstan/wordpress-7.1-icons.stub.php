<?php

class WP_Icon_Collections_Registry
{
	public static function get_instance(): self {}

	public function is_registered(?string $collection_slug): bool {}
}

class WP_Icons_Registry
{
	public static function get_instance(): self {}

	public function is_registered(string $icon_name): bool {}

	/** @return array<string, mixed>|null */
	public function get_registered_icon(string $icon_name): ?array {}
}

/** @param array<string, string> $args */
function wp_register_icon_collection(string $slug, array $args): bool {}

/** @param array<string, string> $args */
function wp_register_icon(string $icon_name, array $args): bool {}

/** @param array<string, mixed> $args */
function wp_get_icon(string $name, array $args = []): string {}
