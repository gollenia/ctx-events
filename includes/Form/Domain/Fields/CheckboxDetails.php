<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Enums\FieldType;
use Contexis\Events\Form\Domain\Enums\ValidationError;

final class CheckboxDetails implements FieldDetails
{
	public function __construct(
		public readonly bool $defaultValue = false,
		public readonly ?string $requiredMessage = null,
		public readonly CheckboxVariant $variant = CheckboxVariant::DEFAULT
	) {}

	public function getType(): FieldType
	{
		return FieldType::CHECKBOX;
	}

	public function toArray(): array
	{
		return [
			'default' => $this->defaultValue,
			'variant' => $this->variant->value,
			'requiredMessage' => $this->requiredMessage,
			'type' => $this->getType()->value,
		];
	}

	public function validateValue(mixed $value): ?ValidationError
	{
		return is_bool($value)
			? null
			: ValidationError::INVALID_FORMAT;
	}

	public function hydrate(mixed $value): mixed
	{
		return in_array($value, [true, 1, '1', 'on', 'yes', 'true'], true);
	}

	public function isEmpty(mixed $value): bool
	{
		return $value === false;
	}
}
