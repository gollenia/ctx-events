<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Icons;

use Contexis\Events\Platform\Wordpress\PluginInfo;

final class IconRegistry
{
	private const COLLECTION = 'ctx-events';

	/** @var array<string, string> */
	private array $icons = [];

	private bool $booted = false;

	public function boot(): void
	{
		if ($this->booted) {
			return;
		}

		if (!function_exists('wp_register_icon_collection') || !function_exists('wp_register_icon')) {
			return;
		}

		$collections = \WP_Icon_Collections_Registry::get_instance();
		if (!$collections->is_registered(self::COLLECTION)) {
			wp_register_icon_collection(self::COLLECTION, [
				'label' => __('Events', 'ctx-events'),
			]);
		}

		foreach ($this->pluginIconPaths() as $name => $pluginPath) {
			$iconName = $this->qualifiedName($name);
			$iconPath = $this->locateThemeIcon($name) ?? $pluginPath;

			if (!\WP_Icons_Registry::get_instance()->is_registered($iconName)) {
				wp_register_icon($iconName, [
					'label' => $this->labelFor($name),
					// The core icon sanitizer deliberately allows `fill` on paths,
					// but not on the SVG root. Normalise the common SVG shorthand
					// (`<svg fill="currentColor">`) before registration so its colour
					// remains identical in the editor and on the frontend.
					'content' => $this->normaliseIconContent($iconPath),
				]);
			}

			if (\WP_Icons_Registry::get_instance()->is_registered($iconName)) {
				$this->icons[$name] = $iconName;
			}
		}

		$this->booted = true;
	}

	public function resolveSlot(string $icon): string
	{
		return trim($icon);
	}

	public function qualifiedName(string $icon): string
	{
		return self::COLLECTION . '/' . $this->resolveSlot($icon);
	}

	/** @return array<string, string> */
	public function getIcons(): array
	{
		$this->boot();

		$icons = [];
		foreach ($this->icons as $name => $iconName) {
			$icon = \WP_Icons_Registry::get_instance()->get_registered_icon($iconName);
			$content = $icon['content'] ?? '';

			if (is_string($content) && $content !== '') {
				$icons[$name] = $content;
			}
		}

		return $icons;
	}

	public function getIconMarkup(string $icon): string
	{
		return $this->getIcons()[$this->resolveSlot($icon)] ?? '';
	}

	/** @return array<string, string> */
	public function getEditorIcons(): array
	{
		return $this->getIcons();
	}

	/** @return array<string, string> */
	private function pluginIconPaths(): array
	{
		$paths = glob(PluginInfo::getPluginDir('/assets/icons/*.svg')) ?: [];
		$icons = [];

		foreach ($paths as $path) {
			$name = pathinfo($path, PATHINFO_FILENAME);
			if ($name !== '') {
				$icons[$name] = $path;
			}
		}

		return $icons;
	}

	private function locateThemeIcon(string $name): ?string
	{
		$path = locate_template('plugins/ctx-events/icons/' . $name . '.svg', false, false);

		return $path !== '' ? $path : null;
	}

	private function normaliseIconContent(string $path): string
	{
		$content = file_get_contents($path);
		if (!is_string($content)) {
			return '';
		}

		if (!preg_match('/<svg\\b[^>]*\\bfill=["\\\']currentColor["\\\'][^>]*>/i', $content)) {
			return $content;
		}

		return (string) preg_replace_callback(
			'/<path\\b([^>]*)>/i',
			static function (array $matches): string {
				if (preg_match('/\\bfill\\s*=/i', $matches[1])) {
					return $matches[0];
				}

				return '<path fill="currentColor"' . $matches[1] . '>';
			},
			$content,
		);
	}

	private function labelFor(string $name): string
	{
		return ucwords(str_replace(['-', '_'], ' ', $name));
	}
}
