<?php

namespace Contexis\Events\Domain\ValueObjects;

/**
 * @immutable
 *
 * A value object representing a permanent link (permalink) to an entity.
 */
final class PermaLink
{
    public function __construct(
        public readonly string $iri,
        public readonly string $slug,
        public readonly string $url
    ) {
    }
}
