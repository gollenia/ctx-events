<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;

final class FormField
{
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly bool $required,
        public readonly FieldWidth $width = FieldWidth::SIX,
        public readonly ?string $description = null,
		public readonly string $customErrorMessage = '',
		public readonly FieldDetails $details,
		public readonly ?VisibilityRule $visibilityRule = null,
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
			'visibilityRule' => $this->visibilityRule?->toArray(),
			...$this->details->toArray(),
		];
	}

	public function shouldValidate(array $allFormData): bool
    {
        if ($this->visibilityRule === null) {
            return true;
        }

        return $this->visibilityRule->isMet($allFormData);
    }

	public function validate(mixed $value): bool
	{
		return $this->details->validateValue($value);
	}
}
