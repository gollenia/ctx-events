<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Contracts;

interface StatusCountsInterface
{

	/**
	 * @param array<string, int> $data
	 */
	public static function fromArray(array $data): static;

	/**
	 * @return array<string, int>
	 */
	public function toArray(): array;
}