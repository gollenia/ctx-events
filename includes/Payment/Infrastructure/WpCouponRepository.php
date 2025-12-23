<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Payment\Domain\Coupon;
use Contexis\Events\Payment\Domain\CouponId;
use Contexis\Events\Payment\Domain\CouponRepository;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

class WpCouponRepository implements CouponRepository
{
	public function find(CouponId $id): ?Coupon
	{
		$post = new \WP_Query([
			'post_type' => 'coupon',
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'p' => $id->toInt(),
		]);

		if (!$post->have_posts()) {
			return null;
		}

		return CouponMapper::map(PostSnapshot::fromWpPost($post->next_post()));
	}

	public function get(?CouponId $id): Coupon
	{
		return $this->find($id);
	}

	public function findByCode(string $code): ?Coupon
	{
		$post = new \WP_Query([
			'post_type' => 'coupon',
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'meta_query' => [
				[
					'key' => CouponMeta::CODE,
					'value' => $code,
					'compare' => '=',
				]
			]
		]);

		if (!$post->have_posts()) {
			return null;
		}

		return CouponMapper::map(PostSnapshot::fromWpPost($post->next_post()));
	}
}