<?php

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface HasTaxonomies
{
    public function registerTaxonomies(): void;
}
