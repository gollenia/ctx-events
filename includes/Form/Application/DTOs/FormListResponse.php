<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Application\DTOs;

use Contexis\Events\Shared\Domain\Abstract\DtoCollection;

final readonly class FormListResponse extends DtoCollection
{
	
	public static function from(FormListItem ...$forms): self
    {
        return new self($forms);
    }


	
}
