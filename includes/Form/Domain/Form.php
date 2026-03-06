<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain;

use Contexis\Events\Form\Domain\Fields\FormFieldCollection;
use Contexis\Events\Form\Domain\Enums\FormType;
use Contexis\Events\Form\Domain\ValueObjects\ValidationResult;

abstract class Form
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
			'fields' => array_map(fn($f) => $f->toArray(), $this->fields->toArray()),
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

			$fieldValue = $formData[$field->name] ?? null;

			$error = $field->validate($fieldValue);
			if ($error !== null) {
				$allErrors[$field->name] = $error->value;
				continue;
			}

			$validatedData[$field->name] = $field->details->hydrate($fieldValue);
		}

		if (empty($allErrors)) {
			return ValidationResult::valid($validatedData);
		}

		return ValidationResult::invalid($allErrors);
	}
}
