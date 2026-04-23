<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure\Bindings;

use WP_Block;
use WP_HTML_Tag_Processor;

trait SupportsCoverImageBindings
{
	/**
	 * @param string[] $supported_attributes
	 * @return string[]
	 */
	public function extendCoverSupportedAttributes(array $supported_attributes): array
	{
		foreach (['url', 'id', 'alt'] as $attribute) {
			if (!in_array($attribute, $supported_attributes, true)) {
				$supported_attributes[] = $attribute;
			}
		}

		return $supported_attributes;
	}

	/**
	 * @param array<string, mixed> $parsed_block
	 */
	public function renderBoundCover(string $block_content, array $parsed_block, WP_Block $block_instance): string
	{
		if (!$this->hasImageBinding($parsed_block)) {
			return $block_content;
		}

		$image_url = $this->getBoundImageUrl($block_instance);
		$image_alt = $this->getBoundImageAlt($block_instance);
		$image_id = $this->getBoundImageId($block_instance);

		if ($image_url === '') {
			return $block_content;
		}

		$processor = new WP_HTML_Tag_Processor($block_content);
		while ($processor->next_tag('IMG')) {
			$class_name = (string) $processor->get_attribute('class');
			if (!str_contains($class_name, 'wp-block-cover__image-background')) {
				continue;
			}

			$processor->set_attribute('src', $image_url);
			$processor->set_attribute('alt', $image_alt);

			if ($image_id > 0) {
				$updated_class_name = preg_match('/\bwp-image-\d+\b/', $class_name) === 1
					? (string) preg_replace('/\bwp-image-\d+\b/', "wp-image-{$image_id}", $class_name)
					: trim($class_name . " wp-image-{$image_id}");

				$processor->set_attribute('class', $updated_class_name);
			}

			return $processor->get_updated_html();
		}

		return $block_content;
	}

	/**
	 * @param array<string, mixed> $parsed_block
	 */
	private function hasImageBinding(array $parsed_block): bool
	{
		$bindings = $parsed_block['attrs']['metadata']['bindings'] ?? null;
		if (!is_array($bindings)) {
			return false;
		}

		foreach (['url', 'id', 'alt'] as $attribute) {
			$binding = $bindings[$attribute] ?? null;
			if (
				is_array($binding) &&
				($binding['source'] ?? null) === static::SOURCE_NAME
			) {
				return true;
			}
		}

		return false;
	}

	abstract protected function getBoundImageUrl(WP_Block $block_instance): string;

	abstract protected function getBoundImageAlt(WP_Block $block_instance): string;

	abstract protected function getBoundImageId(WP_Block $block_instance): int;
}
