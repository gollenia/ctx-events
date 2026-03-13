<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

readonly class FormSummaryCollection extends Collection
{
    public static function from(FormSummary ...$forms): self
    {
        return new self($forms);
    }
}
