<?php

namespace Contexis\Events\Event\Presentation\Resources;

use Contexis\Events\Event\Application\DTOs\EventResponse;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Contexis\Events\Shared\Presentation\RestRoute;
use Contexis\Events\Shared\Presentation\Resources\Schema;
use Contexis\Events\Shared\Presentation\Resources\SchemaResource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Event')]
final readonly class EventResource implements Resource
{
	public function __construct(

		public int $id,
		public string $name,
		public ?string $description,
		public string $status,
		public string $startDate,
		public ?string $endDate,
		public ?string $audience,
		public ?EventBookingSummaryResource $bookingSummary,
		public ?EventIncludeResource $includes,
		public ?SchemaResource $schema,

	) {
	}

	public static function fromDto(EventResponse $eventResponse, RestRoute $route): self
	{
		return new self(
			id: $eventResponse->id,
			name: $eventResponse->name,
			description: $eventResponse->description,
			status: $eventResponse->status->value,
			startDate: $eventResponse->startDate->format(DATE_ATOM),
			endDate: $eventResponse->endDate?->format(DATE_ATOM),
			audience: $eventResponse->audience,
			bookingSummary: $eventResponse->bookingSummary ? EventBookingSummaryResource::from($eventResponse->bookingSummary) : null,
			includes: EventIncludeResource::fromDto($eventResponse),
			schema: $route->getSchema($eventResponse->id)
		);
	}

	public function toArray(): array
	{
		return [
			...$this->schema?->toArray(),
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'status' => $this->status,
			'startDate' => $this->startDate,
			'endDate' => $this->endDate,
			'audience' => $this->audience,
			'bookingSummary' => $this->bookingSummary,
			'includes' => $this->includes,
			'schema' => $this->schema
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}

