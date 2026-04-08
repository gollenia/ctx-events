<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;
use Contexis\Events\Shared\Infrastructure\Icons\IconRegistry;

final class RegisterEventIcons implements Registrar
{
    /** @var array<string, array{file: string}>|null */
    private ?array $definitions = null;

    public function hook(): void
    {
        add_action('ctx_icons_register', [$this, 'register']);
    }

    public function register(IconRegistry $registry): void
    {
        $source = 'ctx-events';
        $basePath = dirname(__FILE__, 4) . '/assets/icons/';

        foreach ($this->definitions() as $name => $definition) {
            $registry->registerFromFile($name, $basePath . $definition['file'], $source);
        }
    }

    /** @return array<string, array{file: string}> */
    private function definitions(): array
    {
        if ($this->definitions !== null) {
            return $this->definitions;
        }

        $definitions = require __DIR__ . '/icon-definitions.php';

        $this->definitions = is_array($definitions) ? $definitions : [];

        return $this->definitions;
    }
}
