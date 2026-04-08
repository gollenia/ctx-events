<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure\Tiptap;

use Tiptap\Core\Mark;

final class UnderlineMark extends Mark
{
    public static $name = 'underline';

    public function parseHTML()
    {
        return [
            ['tag' => 'u'],
            ['tag' => 'span[style*="underline"]'],
        ];
    }

    public function renderHTML($mark, $HTMLAttributes = [])
    {
        return ['u', $HTMLAttributes, 0];
    }
}
