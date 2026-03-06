<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

use Contexis\Events\Event\Domain\Enums\TimeScope;
use Contexis\Events\Shared\Application\Contracts\Criteria;
use Contexis\Events\Shared\Application\Contracts\DTO;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Infrastructure\ValueObjects\Order;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;

final class EventCriteria implements DTO
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly OrderBy $orderBy,
        public readonly TimeScope $scope = TimeScope::FUTURE,
        public readonly array $categories = [],
        public readonly array $tags = [],
		public readonly ?StatusList $status = null,
        public readonly ?int $location = null,
        public readonly ?int $person = null,
		public readonly ?bool $isFree = null,
        public readonly ?bool $bookable = null,
        public readonly ?string $search = null
    ) {
    }
}
