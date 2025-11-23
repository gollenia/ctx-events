<?php

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class OptionsRegistrar implements Registrar
{
    private array $options = [];

    public function __construct(array $options)
    {
        foreach ($options as $option) {
            $this->options = array_merge($this->options, $option->fields());
        }
    }

    public function hook(): void
    {
        $migration = new OptionsMigration($this);
        $migration->migrate();
    }

    public function all(): array
    {
        return $this->options;
    }
}
