<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

readonly class FormSummaryCollection extends Collection
{
    public function __construct(
        FormSummary ...$forms
    ) {
        parent::__construct($forms);
    }
}
