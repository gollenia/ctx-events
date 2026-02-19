<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Presentation\Resources;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Schema')]
final class SchemaResource implements \JsonSerializable
{

	public const CONTEXT_URL = 'https://schema.org/';
	public function __construct(
		public string $context,
		public string $type,
		public string $id,

	) {
	}

	public static function from(string $type, string $iri): self
	{
		return new self(
			context: self::CONTEXT_URL,
			type: ucfirst($type),
			id: $iri
		);
	}

	public function toArray(): array
	{
		return [
			'@context' => $this->context,
			'@type' => $this->type,
			'@id' => $this->id,
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}