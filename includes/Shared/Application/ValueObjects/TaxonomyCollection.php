<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Application\ValueObjects;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class TaxonomyCollection extends Collection
{
    public function all(): array
    {
        return $this->items;
    }

    public function forTaxonomy(string $taxonomy): array
    {
        return array_values(
            array_filter(
                $this->items,
                fn (Taxonomy $term) => $term->taxonomy === $taxonomy
            )
        );
    }

	public function toArray(): array
	{
		return parent::toArray();
	}
}
