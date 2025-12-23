<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Contracts;

use Contexis\Events\Form\Domain\ValueObjects\FieldType;

interface FieldDetails
{
	public function getType(): FieldType;

	public function validateValue(mixed $value): bool;

	public function toArray(): array;

	public function isEmpty(mixed $value): bool;
}