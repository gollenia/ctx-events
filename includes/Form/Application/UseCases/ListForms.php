<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Application\UseCases;

use Contexis\Events\Form\Application\DTOs\FormCriteria;
use Contexis\Events\Form\Application\DTOs\FormListResponse;
use Contexis\Events\Form\Domain\FormRepository;

final class ListForms
{
	public function __construct(
		public FormRepository $formRepository,
	) {
	}

	public function execute(FormCriteria $criteria): FormListResponse
	{
		$forms = $this->formRepository->findByCriteria($criteria);
		$statusCounts = $this->formRepository->getCountsByStatus();

		return $forms->withStatusCounts($statusCounts);
	}
}
