<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\ValueObjects;

final readonly class Link
{
	private function __construct(
		private string $url,
	) {
	}

	public static function fromString(string $url): self
	{
		if (filter_var($url, FILTER_VALIDATE_URL) === false) {
			throw new \InvalidArgumentException("Invalid URL: {$url}");
		}

		if (str_starts_with($url, 'http://')) {
			$url = 'https://' . substr($url, 7);
		}
		return new self($url);
	}

	public function __toString(): string
	{
		return $this->url;
	}
}