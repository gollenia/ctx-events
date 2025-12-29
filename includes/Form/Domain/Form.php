<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain;

use Contexis\Events\Form\Domain\ValueObjects\FormFieldCollection;
use Contexis\Events\Form\Domain\ValueObjects\FormType;
use Contexis\Events\Form\Domain\ValueObjects\ValidationResult;

class Form
{
	public function __construct(
		public readonly FormId $id,
		public readonly FormType $type,
		public readonly FormFieldCollection $fields,
		public readonly string $name,
		public readonly ?string $description,
	) {}

	public function toArray(): array
	{
		return [
			'id' => $this->id->toInt(),
			'type' => $this->type->value,
			'fields' => $this->fields->toArray(),
			'name' => $this->name,
			'description' => $this->description,
		];
	}

	public function validate(array $formData): ValidationResult
	{
		$allErrors = [];
		$validatedData = [];

		foreach ($this->fields as $field) {
			if (!$field->shouldValidate($formData)) {
				continue;
			}

			if (!$field->validate($formData[$field->name])) {
				$allErrors[$field->name] = $field->customErrorMessage;
				continue;
			}

			$validatedData[$field->name] = $field->validate($formData[$field->name]);
		}

		if (empty($allErrors)) {
			return ValidationResult::valid($validatedData);
		}

		return ValidationResult::invalid($allErrors);
	}
}
