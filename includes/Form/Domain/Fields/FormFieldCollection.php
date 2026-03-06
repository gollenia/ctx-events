<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Fields;

use Contexis\Events\Shared\Domain\Abstract\Collection;

/**
 * @extends Collection<FormField>
 */
final readonly class FormFieldCollection extends Collection
{
    public function __construct(
        FormField ...$fields
    ) {
        $this->items = $fields;
    }

}
