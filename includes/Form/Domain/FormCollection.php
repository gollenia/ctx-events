<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

class FormCollection extends Collection
{
    public function __construct(
        Form ...$forms
    ) {
        $this->items = $forms;
    }
}
