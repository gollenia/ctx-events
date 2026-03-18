<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Presentation\Resources;

use Contexis\Events\Shared\Domain\ValueObjects\Address;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript (name: 'Address')]
final readonly class AddressResource implements Resource
{
    public function __construct(
        public ?string $streetAddress,   
        public ?string $addressLocality, 
        public ?string $postalCode,      
        public ?string $addressRegion,   
        public ?string $addressCountry   
    ) {}

	public static function fromValueObject(Address $addressDto): self
	{
		return new self(
			streetAddress: $addressDto->streetAddress,
			addressLocality: $addressDto->addressLocality,
			postalCode: $addressDto->postalCode,
			addressRegion: $addressDto->addressRegion,
			addressCountry: $addressDto->addressCountry
		);
	}

	/**
	 * @return array<string, string>
	 */ 
	public function jsonSerialize(): array
	{
		return [
			'streetAddress' => $this->streetAddress,
			'addressLocality' => $this->addressLocality,
			'postalCode' => $this->postalCode,
			'addressRegion' => $this->addressRegion,
			'addressCountry' => $this->addressCountry
		];
	}


}