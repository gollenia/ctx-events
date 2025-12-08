<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Domain;

use Contexis\Events\Shared\Application\Contracts\Criteria;

final class PersonCriteria implements Criteria
{
    public function __construct(
        public readonly int $page = 0,
        public readonly int $perPage = 10,
        public readonly array $status = ['publish'],
        public readonly array $include = [],
        public readonly array $categories = [],
        public readonly array $tags = [],
        public readonly ?string $search = null
    ) {
    }
}
