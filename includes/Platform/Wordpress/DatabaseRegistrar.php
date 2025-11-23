<?php

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class DatabaseRegistrar implements Registrar
{
    private array $migrations = [];

    public function __construct(array $migrations)
    {
        $this->migrations = $migrations;
    }

    public function hook(): void
    {
        $migration = new DatabaseMigration($this);
        $migration->migrate();
    }

    public function all(): array
    {
        return $this->migrations;
    }
}
