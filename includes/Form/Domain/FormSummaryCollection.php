<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

class FormSummaryCollection extends Collection
{
    public function __construct(
        FormSummary ...$forms
    ) {
        $this->items = $forms;
    }
}
