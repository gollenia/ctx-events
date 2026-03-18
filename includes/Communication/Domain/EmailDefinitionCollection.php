<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain;

use Contexis\Events\Communication\Domain\ValueObjects\EmailContext;
use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class EmailDefinitionCollection extends Collection
{
    public static function from(EmailDefinition ...$definitions): self
    {
        return new self($definitions);
    }

    public function resolve(EmailContext $context): ?EmailDefinition
    {
        $bestMatch = null;
        $bestPriority = -1;

        foreach ($this->items as $definition) {
            $priority = $definition->priorityFor($context);

            if ($priority <= $bestPriority) {
                continue;
            }

            $bestMatch = $definition;
            $bestPriority = $priority;
        }

        return $bestMatch;
    }
}
