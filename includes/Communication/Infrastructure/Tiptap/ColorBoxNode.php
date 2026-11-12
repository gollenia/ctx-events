<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure\Tiptap;

use Tiptap\Core\Node;

final class ColorBoxNode extends Node
{
    public static $name = 'colorBox';

    public function addAttributes()
    {
        return [
            'variant' => [
                'default' => 'info',
                'parseHTML' => static fn ($DOMNode): string => (string) ($DOMNode->getAttribute('data-variant') ?: 'info'),
            ],
        ];
    }

    public function parseHTML()
    {
        return [['tag' => 'div[data-type="color-box"]']];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        $variant = in_array($node->attrs->variant ?? '', ['warning', 'success'], true)
            ? $node->attrs->variant
            : 'info';
        $styles = [
            'info' => 'background:#e8f0fe;border-left:4px solid #0b57d0;',
            'warning' => 'background:#fef7e0;border-left:4px solid #b06000;',
            'success' => 'background:#e6f4ea;border-left:4px solid #137333;',
        ];

        return [
            'div',
            [
                ...$HTMLAttributes,
                'data-type' => 'color-box',
                'data-variant' => $variant,
                'style' => $styles[$variant] . 'margin:12px 0;padding:12px 16px;',
            ],
            0,
        ];
    }
}
