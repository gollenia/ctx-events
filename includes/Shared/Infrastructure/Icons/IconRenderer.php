<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Icons;

final class IconRenderer
{
    public function __construct(
        private readonly IconRegistry $registry
    ) {}

    public function render(string $icon, array $attributes = []): string
    {
        $resolvedIcon = $this->registry->resolveSlot($icon);
        $markup = $this->registry->getIconMarkup($icon);

        if ($markup === '') {
            return '';
        }

        $className = trim('ctx-events-icon ' . (string) ($attributes['class'] ?? ''));
        $wrapperAttributes = [
            'class' => $className,
            'data-ctx-icon' => $resolvedIcon,
            'aria-hidden' => 'true',
        ];

        if (!empty($attributes['title']) && is_string($attributes['title'])) {
            $wrapperAttributes['aria-label'] = $attributes['title'];
            unset($wrapperAttributes['aria-hidden']);
        }

        $html = sprintf(
            '<span %s>%s</span>',
            $this->buildAttributes($wrapperAttributes),
            $this->sanitizeSvg($markup),
        );

        return (string) apply_filters(
            'ctx_events_resolved_icon',
            apply_filters('ctx_events_block_icon', $html, $icon),
            $icon,
            $resolvedIcon,
            $attributes,
        );
    }

    private function sanitizeSvg(string $markup): string
    {
        return (string) wp_kses($markup, [
            'svg' => [
                'aria-hidden' => true,
                'class' => true,
                'fill' => true,
                'height' => true,
                'role' => true,
                'stroke' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'stroke-width' => true,
                'viewBox' => true,
                'width' => true,
                'xmlns' => true,
            ],
            'g' => [
                'fill' => true,
                'stroke' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'stroke-width' => true,
                'transform' => true,
            ],
            'path' => [
                'd' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'stroke-width' => true,
                'transform' => true,
            ],
            'circle' => [
                'cx' => true,
                'cy' => true,
                'fill' => true,
                'r' => true,
                'stroke' => true,
                'stroke-width' => true,
            ],
            'ellipse' => [
                'cx' => true,
                'cy' => true,
                'fill' => true,
                'rx' => true,
                'ry' => true,
                'stroke' => true,
                'stroke-width' => true,
            ],
            'line' => [
                'stroke' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'stroke-width' => true,
                'x1' => true,
                'x2' => true,
                'y1' => true,
                'y2' => true,
            ],
            'polygon' => [
                'fill' => true,
                'points' => true,
                'stroke' => true,
                'stroke-width' => true,
            ],
            'polyline' => [
                'fill' => true,
                'points' => true,
                'stroke' => true,
                'stroke-width' => true,
            ],
            'rect' => [
                'fill' => true,
                'height' => true,
                'rx' => true,
                'ry' => true,
                'stroke' => true,
                'stroke-width' => true,
                'width' => true,
                'x' => true,
                'y' => true,
            ],
            'title' => [],
            'desc' => [],
        ]);
    }

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
