<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Dtos;

final readonly class CouponExportData
{
    /**
     * @param CouponExportSheet[] $sheets
     */
    public function __construct(
        public string $fileName,
        public array $sheets,
    ) {
    }
}
