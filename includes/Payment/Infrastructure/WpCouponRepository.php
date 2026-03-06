<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Payment\Domain\Coupon;
use Contexis\Events\Payment\Domain\CouponCollection;
use Contexis\Events\Payment\Domain\CouponId;
use Contexis\Events\Payment\Domain\CouponRepository;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

class WpCouponRepository implements CouponRepository
{
	public function find(CouponId $id): ?Coupon
	{
		$post = new \WP_Query([
			'post_type' => CouponPost::POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'p' => $id->toInt(),
		]);

		if (!$post->have_posts()) {
			return null;
		}

		$wpPost = $post->next_post();
        if (!$wpPost instanceof \WP_Post) {
            return null;
        }

		return CouponMapper::map(PostSnapshot::fromWpPost($wpPost));
	}

	public function get(CouponId $id): Coupon
	{
		$coupon = $this->find($id);
        if ($coupon === null) {
            throw new \DomainException('Coupon not found: ' . $id->toInt());
        }

		return $coupon;
	}

	public function findByCode(string $code): ?Coupon
	{
		$post = new \WP_Query([
			'post_type' => CouponPost::POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'meta_query' => [
				'relation' => 'OR',
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

		$wpPost = $post->next_post();
        if (!$wpPost instanceof \WP_Post) {
            return null;
        }

		return CouponMapper::map(PostSnapshot::fromWpPost($wpPost));
	}

    public function findMany(array $ids): CouponCollection
    {
        $couponIds = array_map(static fn (mixed $couponId): int => (int) $couponId, $ids);
        $couponIds = array_filter($couponIds, static fn (int $couponId): bool => $couponId > 0);
        $couponIds = array_values(array_unique($couponIds));

        if ($couponIds === []) {
            return new CouponCollection();
        }

        $query = new \WP_Query([
            'post_type' => CouponPost::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'post__in' => $couponIds,
        ]);

        return CouponMapper::mapCollection($query->posts);
    }

    public function findGlobal(): CouponCollection
    {
        $query = new \WP_Query([
            'post_type' => CouponPost::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => CouponMeta::GLOBAL,
                    'value' => '1',
                    'compare' => '=',
                ],
                [
                    'key' => '_coupon_global',
                    'value' => '1',
                    'compare' => '=',
                ],
            ],
        ]);

        return CouponMapper::mapCollection($query->posts);
    }

}
