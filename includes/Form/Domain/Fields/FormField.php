<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Fields;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Enums\ValidationError;
use Contexis\Events\Form\Domain\Enums\FieldWidth;
use Contexis\Events\Form\Domain\ValueObjects\VisibilityRule;

final class FormField
{
	public function __construct(
		public readonly string $name,
		public readonly string $label,
		public readonly bool $required,
		public readonly FieldDetails $details,
		public readonly FieldWidth $width = FieldWidth::SIX,
		public readonly ?string $description = null,
		public readonly ?VisibilityRule $visibilityRule = null,
	) {}

	public function toArray(): array
	{
		return [
			'name' => $this->name,
			'label' => $this->label,
			'required' => $this->required,
			'width' => $this->width->value,
			'description' => $this->description,
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

	public function validate(mixed $value): ?ValidationError
	{
		if ($this->required && ($value === null || $this->details->isEmpty($value))) {
			return ValidationError::REQUIRED;
		}

		return $this->details->validateValue($value);
	}
}
