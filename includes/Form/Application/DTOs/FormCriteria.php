<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Application\DTOs;

use Contexis\Events\Form\Domain\Enums\FormType;
use Contexis\Events\Shared\Application\Contracts\Criteria;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Infrastructure\ValueObjects\Order;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;

final readonly class FormCriteria implements Criteria
{
	public function __construct(
		public OrderBy $orderBy,
		public ?FormType $type = null,
		public ?string $search = null,
		public int $page = 0,
        public int $perPage = -1,
        public ?StatusList $status = null,
		/* array<string> */
        public ?array $tags = [],
	) {
	}
}