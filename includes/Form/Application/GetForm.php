<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Application;

use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\FormRepository;

class GetForm
{
	public function __construct(
		private FormRepository $formRepository
	) {
	}

	public function execute(FormId $formId): FormDto
	{
		$form = $this->formRepository->get($formId);
		return new FormDto(
			$form->getId()->toString(),
			$form->getTitle(),
			$form->getDescription(),
			$form->getType()
		);
	}
}
