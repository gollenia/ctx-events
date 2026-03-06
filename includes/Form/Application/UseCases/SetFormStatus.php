<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Application\UseCases;

use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\FormRepository;
use Contexis\Events\Shared\Domain\ValueObjects\Status;

final class SetFormStatus
{
	public function __construct(
		private readonly FormRepository $formRepository,
	) {
	}

	public function execute(FormId $formId, Status $status): bool
	{
		$form = $this->formRepository->find($formId);
		if (!$form) {
			return false;
		}

		$this->formRepository->saveStatus($formId, $status);
		return true;
	}
}
