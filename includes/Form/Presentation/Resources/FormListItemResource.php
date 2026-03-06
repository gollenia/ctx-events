<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Presentation\Resources;

use Contexis\Events\Form\Application\DTOs\FormListItem;
use Contexis\Events\Shared\Application\ValueObjects\TaxonomyCollection;
use Contexis\Events\Shared\Presentation\Contracts\Resource;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Form')]
final readonly class FormListItemResource implements Resource
{

	public function __construct(
		public int $id,
		public string $title,
		public ?string $description,
		public string $type,
		public string $createdAt,
		public int $usageCount = 0,
		public TaxonomyCollection $tags,
		public string $status
	) {
	}

	public static function fromDTO(FormListItem $item): self
	{
		return new self(
			id: $item->id->toInt(),
			title: $item->title,
			description: $item->description,
			type: $item->type->value,
			createdAt: $item->createdAt->format(DATE_ATOM),
			usageCount: $item->usageCount,
			tags: $item->tags,
			status: $item->status->value
		);
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'description' => $this->description,
			'type' => $this->type,
			'createdAt' => $this->createdAt,
			'usageCount' => $this->usageCount,
			'tags' => $this->tags->toArray(),
			'status' => $this->status,
		];
	}
}