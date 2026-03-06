<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Application\UseCases;

use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\FormRepository;

final class DeleteForm
{
	public function __construct(
		private readonly FormRepository $formRepository,
	) {
	}

	public function execute(FormId $formId): bool
	{
		return $this->formRepository->delete($formId);
	}
}

