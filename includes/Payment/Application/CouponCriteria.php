<?php

namespace Contexis\Events\Payment\Application;

use Contexis\Events\Shared\Domain\ValueObjects\StatusList;

class CouponCriteria
{
	public function __construct(
        public readonly int $page = 0,
        public readonly int $perPage = 10,
        public readonly ?StatusList $status = null,
        public readonly string $orderBy = 'date-time',
        public readonly string $order = 'DESC',
        public readonly array $tags = [],
        public readonly ?string $search = null
    ) {
    }
}