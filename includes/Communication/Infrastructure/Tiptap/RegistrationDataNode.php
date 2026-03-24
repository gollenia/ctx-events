<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure\Tiptap;

use Tiptap\Core\Node;

final class RegistrationDataNode extends Node
{
    public static $name = 'registrationData';

    public function parseHTML()
    {
        return [
            ['tag' => 'div[data-type="registrationData"]'],
        ];
    }

    public function renderHTML($node)
    {
        $renderer = $this->options['renderRegistrationData'] ?? null;

        if (!is_callable($renderer)) {
            return ['content' => ''];
        }

        return ['content' => (string) $renderer()];
    }
}
