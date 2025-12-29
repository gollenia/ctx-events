<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Contracts;

use Contexis\Events\Form\Domain\Enums\FieldType;
use Contexis\Events\Form\Domain\Enums\ValidationError;

interface FieldDetails
{
	public function getType(): FieldType;
	public function validateValue(mixed $value): ?ValidationError;
	public function toArray(): array;
	public function hydrate(mixed $value): mixed;
	public function isEmpty(mixed $value): bool;
}
