<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;

final class SelectDetails implements FieldDetails
{
    public function __construct(
        public readonly SelectType $selectType,
        public readonly SelectOptions $options,
		public readonly string $placeholder = '',
		public readonly bool $hasNullOption = false,
        public readonly ?string $defaultValue = null,
    ) {
    }

	public function getType(): FieldType
	{
		return FieldType::SELECT;
	}

	public function validateValue(mixed $value): bool
	{
		if (!is_string($value) && !is_int($value)) {
			return false;
		}

		return $this->options->contains($value);
	}

	public function isEmpty(mixed $value): bool
	{
		return $value === '';
	}

	public function toArray(): array
	{
		return [
			'type' => $this->getType()->value,
			'selectType' => $this->selectType->value,
			'options' => $this->options->toArray(),
			'hasNullOption' => $this->hasNullOption,
			'placeholder' => $this->placeholder,
			'defaultValue' => $this->defaultValue,
		];
	}
}