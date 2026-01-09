<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Domain\Enums\TimeScope;
use Contexis\Events\Shared\Application\Contracts\Criteria;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;

final class EventCriteria implements Criteria
{
    public function __construct(
        public readonly int $page = 0,
        public readonly int $perPage = 10,
        public readonly ?StatusList $status = null,
        public readonly ?EventIncludes $includes = null,
        public readonly string $orderBy = 'date-time',
        public readonly string $order = 'DESC',
        public readonly TimeScope $scope = TimeScope::FUTURE,
        public readonly array $categories = [],
        public readonly array $tags = [],
        public readonly ?int $location = null,
        public readonly ?int $person = null,
		public readonly ?bool $isFree = null,
        public readonly ?bool $bookable = null,
        public readonly ?string $search = null
    ) {
    }
}
