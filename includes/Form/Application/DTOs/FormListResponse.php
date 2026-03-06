<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Application\DTOs;

use Contexis\Events\Shared\Application\ValueObjects\Pagination;
use Contexis\Events\Shared\Domain\Abstract\DtoCollection;
use Contexis\Events\Shared\Domain\ValueObjects\StatusCounts;

final readonly class FormListResponse extends DtoCollection
{
	public ?Pagination $pagination;
    public ?StatusCounts $statusCounts;
	
	public function __construct(
        FormListItem ...$forms
    ) {
        $this->items = $forms;
    }

	public function withPagination(Pagination $pagination): self
    {
        return clone($this, ['pagination' => $pagination]);
    }

    public function withStatusCounts(StatusCounts $statusCounts): self
    {
        return clone($this, ['statusCounts' => $statusCounts]);
    }

    public function hasStatusCounts(): bool
    {
        return $this->statusCounts !== null;
    }

	
}