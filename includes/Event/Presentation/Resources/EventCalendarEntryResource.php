<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Presentation\Resources;

use Contexis\Events\Event\Application\DTOs\EventCalendarEntry;

final readonly class EventCalendarEntryResource
{
	/**
	 * @param array<int> $categoryIds
	 */
	public function __construct(
		public int $id,
		public string $title,
		public string $description,
		public string $startDate,
		public string $endDate,
		public array $categoryIds,
		public ?string $color,
		public ?string $locationName,
		public ?string $personName,
	) {
	}

	public static function fromDto(EventCalendarEntry $entry): self
	{
		return new self(
			id: $entry->id,
			title: $entry->title,
			description: $entry->description,
			startDate: $entry->startDate->format(DATE_ATOM),
			endDate: $entry->endDate->format(DATE_ATOM),
			categoryIds: $entry->categoryIds,
			color: $entry->color,
			locationName: $entry->locationName,
			personName: $entry->personName,
		);
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'description' => $this->description,
			'startDate' => $this->startDate,
			'endDate' => $this->endDate,
			'categoryIds' => $this->categoryIds,
			'color' => $this->color,
			'locationName' => $this->locationName,
			'personName' => $this->personName,
		];
	}
}
