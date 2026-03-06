<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain;

use Contexis\Events\Form\Application\DTOs\FormCriteria;
use Contexis\Events\Form\Application\DTOs\FormListResponse;
use Contexis\Events\Form\Domain\Enums\FormType;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\ValueObjects\StatusCounts;

interface FormRepository
{
    public function find(FormId $formId): ?Form;
	public function findByCriteria(FormCriteria $criteria): FormListResponse;
	public function saveStatus(FormId $formId, Status $status): void;
	public function delete(FormId $formId): bool;
	public function duplicate(FormId $formId): ?FormId;
	public function getCountsByStatus(): StatusCounts;
}
