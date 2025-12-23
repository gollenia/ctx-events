<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

final class FieldConfig
{
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly bool $required,
        public readonly FieldWidth $width = FieldWidth::SIX,
        public readonly ?string $description = null,
		public readonly string $customErrorMessage = '',
		public readonly ?string $defaultValue = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'required' => $this->required,
            'width' => $this->width->value,
            'description' => $this->description,
            'customErrorMessage' => $this->customErrorMessage,
			'defaultValue' => $this->defaultValue,
        ];
    }
}