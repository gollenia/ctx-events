<?php

declare(strict_types=1);


namespace Contexis\Events\Form\Application\DTOs;

use Contexis\Events\Form\Domain\Enums\FormType;
use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Shared\Application\ValueObjects\TaxonomyCollection;
use Contexis\Events\Shared\Domain\ValueObjects\Status;

final readonly class FormListItem
{
	public function __construct(
		public FormId $id,
		public string $title,
		public ?string $description,
		public FormType $type,
		public \DateTimeImmutable $createdAt,
		public TaxonomyCollection $tags,
		public Status $status,
		public int $usageCount = 0,
	) {
	}

	public function withUsageCount(int $usageCount): self
	{
		return clone($this, ['usageCount' => $usageCount]);
	}
}