<?php

namespace Contexis\Events\Core\Traits;

use ReflectionClass;

trait ReplicatesProperties
{
    /**
     * Create a new instance of the class, replicating all properties from the current instance,
     * with optional overrides.
     *
     * @param array $overrides Key-value pairs of properties to override in the new instance.
     * @return static A new instance of the class with replicated properties.
     */
    protected function replicate(array $overrides = [], array $ignore = []): static
    {
        $ref = new ReflectionClass($this);
        $props = [];

        foreach ($ref->getProperties() as $prop) {
            $prop->setAccessible(true);
            if (in_array($prop->getName(), $ignore, true)) {
                continue;
            }
            $props[$prop->getName()] = $prop->getValue($this);
        }

        $args = array_merge($props, $overrides);

        return new static(...$args);
    }
}
