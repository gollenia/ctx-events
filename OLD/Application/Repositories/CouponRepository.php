<?php

namespace Contexis\Events\Application\Repositories;

use Contexis\Events\Models\Coupon;

interface CouponRepository {
	public function find_by_id(int $id);
	public function save(Coupon $coupon): void;
	public function delete($coupon): void;
	public function get_global_coupon_ids(): array;
	public function get_event_coupon_ids(int $event_id): array;
}