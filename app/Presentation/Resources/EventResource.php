<?php

namespace Contexis\Events\Presentation\Resources;

use Contexis\Events\Application\DTO\EventDto;
use JsonSerializable;

class EventResource implements JsonSerializable {
	public function __construct(
		public readonly EventDto $event,
	) {}

	public function jsonSerialize(): array {
		return [
			'id' => $this->event->id,
			'title' => $this->event->title,
			'author' => $this->event->author,
			'description' => $this->event->description,
			'status' => $this->event->status->value,
			'schedule' => [
				'start' => $this->event->schedule->start->format('c'),
				'end' => $this->event->schedule->end?->format('c')
			],
			'tickets' => $this->event->tickets ? $this->event->tickets : null,
			'location' => $this->event->location ? [
				'id' => $this->event->location->id,
				'title' => $this->event->location->title
			] : null,
			'image' => $this->event->image ? [
				'id' => $this->event->image->id,
				'thumb_url' => $this->event->image->thumb_url,
				'medium_url' => $this->event->image->medium_url,
				'full_url' => $this->event->image->full_url,
				'alt_text' => $this->event->image->alt_text,
			] : null,
		];
	}
}