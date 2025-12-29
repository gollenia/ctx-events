<?php
declare(strict_types=1);

namespace Contexis\Events\Form\Application;

use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\FormRepository;

class FormValidationService
{
	public function __construct(
        private FormRepository $formRepository
    ) {
	}

    public function validate(FormId $formId, array $data): array
    {
        $form = $this->formRepository->get($formId);

        return $form->validate($data);
    }
}