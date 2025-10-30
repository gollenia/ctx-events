<?php

namespace Contexis\Events\Infrastructure\Persistence;

class CouponRepository extends AbstractPostTypeRepository {

	protected const POST_TYPE_CLASS = \Contexis\Events\Models\Coupon::class;
	protected const MODEL_CLASS = \Contexis\Events\Models\Coupon::class;
	protected const MAPPER_CLASS = \Contexis\Events\Infrastructure\Persistence\CouponMapper::class;
	protected const COLLECTION_CLASS = \Contexis\Events\Collections\CouponCollection::class;
}
