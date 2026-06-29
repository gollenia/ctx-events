<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Dtos;

use Contexis\Events\Shared\Application\Contracts\Criteria;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;

final readonly class CouponCriteria implements Criteria
{
    public function __construct(
        public OrderBy $orderBy,
        public ?string $search = null,
        public int $page = 1,
        public int $perPage = 25,
        public ?StatusList $status = null,
    ) {
    }
}
