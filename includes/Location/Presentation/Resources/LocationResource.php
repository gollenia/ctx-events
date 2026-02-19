<?php
declare(strict_types=1);

namespace Contexis\Events\Location\Presentation\Resources;

use Contexis\Events\Location\Application\LocationDto;
use Contexis\Events\Shared\Domain\ValueObjects\Link;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Contexis\Events\Shared\Presentation\Factories\WpLink;
use Contexis\Events\Shared\Presentation\Links;
use Contexis\Events\Shared\Presentation\Resources\AddressResource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Location')]
class LocationResource implements Resource
{
    public function __construct(
		public int $id,
		/** @var Link $link */
        public Link $link,
		public string $name,
		public AddressResource $address,
		/** @var array<string, float>|null $geoCoordinates */
		public ?array $geoCoordinates
    ) {
    }

	public static function fromDto(LocationDto $locationDto): self
	{		return new self(
			id: $locationDto->id,
			link: WpLink::fromPostId($locationDto->id),
			name: $locationDto->name,
			address: AddressResource::fromValueObject($locationDto->address),
			geoCoordinates: $locationDto->geoCoordinates ? [
				'latitude' => $locationDto->geoCoordinates->latitude,
				'longitude' => $locationDto->geoCoordinates->longitude
			] : null
		);
	}

    public function jsonSerialize(): array
    {
        return [
            'link' => $this->link,
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'geoCoordinates' => $this->geoCoordinates
        ];
    }
}
