<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain;

use Contexis\Events\Form\Domain\Enums\FormType;

interface FormRepository
{
    public function find(FormId $formId): ?Form;
	public function findByType(FormType $formType): ?FormSummaryCollection;
}
