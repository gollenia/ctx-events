<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Payment\Application\Dtos\CouponCriteria;
use Contexis\Events\Payment\Application\Dtos\CouponListResponse;
use Contexis\Events\Shared\Domain\Contracts\StatusCountsInterface;

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
    public function findByCriteria(CouponCriteria $criteria): CouponListResponse;
    public function getCountsByStatus(): StatusCountsInterface;
    public function saveStatus(CouponId $couponId, \Contexis\Events\Shared\Domain\ValueObjects\Status $status): void;
    public function delete(CouponId $couponId): bool;
    /** @return CouponId[] */
    public function duplicateMany(CouponId $couponId, int $count): array;
}
