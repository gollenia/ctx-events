<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Domain;

use Contexis\Events\Shared\Application\Contracts\Criteria;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;

final readonly class PersonCriteria implements Criteria
{

    public function __construct(
        public int $page = 0,
        public int $perPage = 10,
        public ?StatusList $status = null,
        public array $include = [],
		public array $categories = [],
        public array $tags = [],
        public ?string $search = null
    ) {
    }
}
