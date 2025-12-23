<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;

final class CheckboxDetails implements FieldDetails
{
    public function __construct(
        public readonly bool $defaultValue = false,
		public readonly CheckboxVariant $variant = CheckboxVariant::DEFAULT
    ) {
    }

	public function getType(): FieldType
	{
		return FieldType::CHECKBOX;
	}

    public function toArray(): array
    {
		return [
			'default' => $this->defaultValue,
			'variant' => $this->variant->value,
			'type' => $this->getType()->value,
		];
    }

    public function validateValue(mixed $value): bool
    {
        return is_bool($value);
    }

	public function isEmpty(mixed $value): bool
	{
		return $value === false;
	}
}
