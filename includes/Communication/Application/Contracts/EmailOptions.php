<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\Contracts;

use Contexis\Events\Shared\Domain\Contracts\Options;

interface EmailOptions extends Options
{
    public function layout(): string;
    public function css(): string;
}
