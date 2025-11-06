<?php

namespace Contexis\Events\Core;

final class ContainerFactory
{
    public static function build(): \DI\Container
    {
        $builder = new \DI\ContainerBuilder();
        $definitions = require __DIR__ . '/config/container.php';

        if (!\is_array($definitions)) {
            throw new \RuntimeException("DI definitions file must return array, got " . gettype($definitions));
        }

        $builder->addDefinitions($definitions);
        return $builder->build();
    }
}
