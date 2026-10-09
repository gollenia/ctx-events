<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Icons;

final class IconRenderer
{
    public function __construct(
        private readonly IconRegistry $registry
    ) {}

	/**
	 * @param array<string, mixed> $attributes
	 */
    public function render(string $icon, array $attributes = []): string
    {
        $resolvedIcon = $this->registry->resolveSlot($icon);

		if ($resolvedIcon === '') {
            return '';
        }

		$this->registry->boot();
		$iconName = $this->registry->qualifiedName($resolvedIcon);
		if (!\WP_Icons_Registry::get_instance()->is_registered($iconName)) {
			return '';
		}

        $className = trim('ctx-events-icon ' . (string) ($attributes['class'] ?? ''));
        $label = !empty($attributes['title']) && is_string($attributes['title'])
			? $attributes['title']
			: '';
		$markup = wp_get_icon($iconName, [
			'size' => null,
			'class' => $className . '__svg',
			'label' => $label,
		]);

		if ($markup === '') {
			return '';
		}

        $html = sprintf(
            '<span %s>%s</span>',
			$this->buildAttributes([
				'class' => $className,
				'data-ctx-icon' => $resolvedIcon,
				'aria-hidden' => $label === '' ? 'true' : null,
			]),
			$markup,
        );

        return (string) apply_filters(
            'ctx_events_resolved_icon',
            apply_filters('ctx_events_block_icon', $html, $icon),
            $icon,
            $resolvedIcon,
            $attributes,
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function buildAttributes(array $attributes): string
    {
        $parts = [];

        foreach ($attributes as $name => $value) {
            if ($value === '' || $value === null) {
                continue;
            }

            $parts[] = sprintf('%s="%s"', esc_attr((string) $name), esc_attr((string) $value));
        }

        return implode(' ', $parts);
    }
}
