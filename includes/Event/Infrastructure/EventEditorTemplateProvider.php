<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

final class EventEditorTemplateProvider
{
	private const THEME_TEMPLATE = 'plugins/ctx-events/templates/event-editor-template.php';

	/**
	 * @return array<int, mixed>
	 */
	public function get(): array
	{
		$template = $this->loadThemeTemplate() ?? $this->loadPluginTemplate();

		return apply_filters(
			'ctx_events_event_editor_template',
			$template,
			EventPost::POST_TYPE
		);
	}

	/**
	 * @return array<int, mixed>|null
	 */
	private function loadThemeTemplate(): ?array
	{
		$templatePath = locate_template(self::THEME_TEMPLATE, false, false);

		if ($templatePath === '') {
			return null;
		}

		return $this->loadTemplate($templatePath);
	}

	/**
	 * @return array<int, mixed>
	 */
	private function loadPluginTemplate(): array
	{
		$template = $this->loadTemplate(dirname(__DIR__) . '/Presentation/Templates/event-editor-template.php');

		if ($template === null) {
			throw new \RuntimeException('The default event editor template must return an array.');
		}

		return $template;
	}

	/**
	 * @return array<int, mixed>|null
	 */
	private function loadTemplate(string $templatePath): ?array
	{
		if (!is_file($templatePath)) {
			return null;
		}

		$template = require $templatePath;

		return is_array($template) ? $template : null;
	}
}
