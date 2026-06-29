<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Dtos;

final readonly class CouponExportSheet
{
    /**
     * @param array<int, array<int, scalar|null>> $rows
     */
    public function __construct(
        public string $name,
        public array $rows,
    ) {
    }
}
