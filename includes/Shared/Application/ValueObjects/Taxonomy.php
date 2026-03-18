<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Application\ValueObjects;

final class Taxonomy
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $name,
        public readonly string $taxonomy,
		public readonly ?string $description = null
    ) {
    }

	/**
	 * @return array<string, mixed>
	 */
	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'slug' => $this->slug,
			'name' => $this->name,
			'taxonomy' => $this->taxonomy,
			'description' => $this->description
		];
	}
}
