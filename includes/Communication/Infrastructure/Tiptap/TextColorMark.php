<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure\Tiptap;

use Tiptap\Core\Mark;

final class TextColorMark extends Mark
{
    public static $name = 'textColor';

    public function addAttributes()
    {
        return [
            'color' => [
                'parseHTML' => function ($DOMNode) {
                    $color = $DOMNode->getAttribute('data-color');

                    if (is_string($color) && trim($color) !== '') {
                        return trim($color);
                    }

                    $style = $DOMNode->getAttribute('style');
                    if (!is_string($style) || trim($style) === '') {
                        return null;
                    }

                    if (!preg_match('/color\s*:\s*([^;]+)/i', $style, $matches)) {
                        return null;
                    }

                    return trim($matches[1]);
                },
                'renderHTML' => function ($attributes): array {
                    $color = $attributes->color ?? null;

                    if (!is_string($color) || trim($color) === '') {
                        return [];
                    }

                    $sanitized = trim($color);

                    return [
                        'data-color' => $sanitized,
                        'style' => sprintf('color: %s', $sanitized),
                    ];
                },
            ],
        ];
    }

    public function parseHTML()
    {
        return [
            ['tag' => 'span[data-color]'],
            ['tag' => 'span[style*="color"]'],
        ];
    }

    public function renderHTML($mark, $HTMLAttributes = [])
    {
        return ['span', $HTMLAttributes, 0];
    }
}
