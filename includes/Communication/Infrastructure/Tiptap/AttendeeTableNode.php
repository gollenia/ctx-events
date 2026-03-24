<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure\Tiptap;

use Tiptap\Core\Node;

final class AttendeeTableNode extends Node
{
    public static $name = 'attendeeTable';

    public function parseHTML()
    {
        return [
            ['tag' => 'div[data-type="attendeeTable"]'],
        ];
    }

    public function renderHTML($node)
    {
        $renderer = $this->options['renderAttendeeTable'] ?? null;

        if (!is_callable($renderer)) {
            return ['content' => ''];
        }

        return ['content' => (string) $renderer()];
    }
}
