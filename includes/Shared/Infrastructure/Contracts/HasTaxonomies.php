<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface HasTaxonomies
{
    public function registerTaxonomies(): void;
}
