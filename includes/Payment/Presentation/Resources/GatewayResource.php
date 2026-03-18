<?php

declare (strict_types=1);

namespace Contexis\Events\Payment\Presentation\Resources;

use Contexis\Events\Payment\Application\Dtos\GatewayListItemDto;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Gateway')]
final readonly class GatewayResource implements Resource
{
	/** @param array<mixed> $settings */
	public function __construct(
		public string $slug,
		public string $title,
		public string $adminName,
		public bool $enabled,
        public bool $supportsCheckoutLink,
		public array $settings,
	) {
	}

	public static function fromDto(GatewayListItemDto $dto): self
	{
		return new self(
			slug: $dto->slug,
			title: $dto->title,
			adminName: $dto->adminName,
			enabled: $dto->active,
            supportsCheckoutLink: $dto->supportsCheckoutLink,
			settings: [
				'description' => $dto->description,
			],
		);
	}

	public function jsonSerialize(): mixed
	{
		return [
			'slug' => $this->slug,
			'title' => $this->title,
			'adminName' => $this->adminName,
			'enabled' => $this->enabled,
            'supportsCheckoutLink' => $this->supportsCheckoutLink,
			'settings' => $this->settings,
		];
	}
}
