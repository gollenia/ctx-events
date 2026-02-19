<?php

namespace Contexis\Events\Event\Presentation\Resources;

use Contexis\Events\Event\Application\DTOs\EventResponse;
use Contexis\Events\Location\Presentation\Resources\LocationResource;
use Contexis\Events\Media\Presentation\Resources\ImageResource;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'EventIncludes')] 
final class EventIncludeResource implements Resource
{
	public function __construct(
		public ?ImageResource $image,
		public ?LocationResource $location,
		public ?array $categories,
		public ?array $tags,
	) {
	}

	public static function fromDto(EventResponse $eventResponse): self
	{
		$includes = [];

        if ($eventResponse->locationDto) {
            $includes['location'] = LocationResource::fromDto($eventResponse->locationDto);
        }

        if ($eventResponse->imageDto) {
            $includes['image'] = ImageResource::fromDto($eventResponse->imageDto);
        }

        if ($eventResponse->categories) {
            $includes['categories'] = $eventResponse->categories->toArray();
        }

        if ($eventResponse->tags) {
            $includes['tags'] = $eventResponse->tags->toArray();
        }

		return new self(
			image: $includes['image'] ?? null,
			location: $includes['location'] ?? null,
			categories: $includes['categories'] ?? null,
			tags: $includes['tags'] ?? null,
		);
	}

	public function jsonSerialize(): mixed
	{
		return [
			'image' => $this->image,
			'location' => $this->location,
			'categories' => $this->categories,
			'tags' => $this->tags,
		];
	}
}