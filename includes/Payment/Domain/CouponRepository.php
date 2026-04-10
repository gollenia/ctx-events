<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Payment\Application\CouponCriteria;

interface CouponRepository
{
    public function find(CouponId $id): ?Coupon;
    public function findByCode(string $code): ?Coupon;
	/**
	 * @param array<int> $ids
	 */
    public function findMany(array $ids): CouponCollection;
    public function findGlobal(): CouponCollection;
    public function get(CouponId $id): Coupon;
}
