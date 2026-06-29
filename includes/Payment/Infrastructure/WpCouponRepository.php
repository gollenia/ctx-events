<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Payment\Application\Dtos\CouponCriteria;
use Contexis\Events\Payment\Application\Dtos\CouponListResponse;
use Contexis\Events\Payment\Domain\Coupon;
use Contexis\Events\Payment\Domain\CouponCollection;
use Contexis\Events\Payment\Domain\CouponId;
use Contexis\Events\Payment\Domain\CouponRepository;
use Contexis\Events\Payment\Infrastructure\Mappers\CouponListItemMapper;
use Contexis\Events\Shared\Application\ValueObjects\Pagination;
use Contexis\Events\Shared\Domain\Contracts\StatusCountsInterface;
use Contexis\Events\Shared\Infrastructure\Wordpress\InteractsWithStatusCounts;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

class WpCouponRepository implements CouponRepository
{
    use InteractsWithStatusCounts;

    public function __construct(
        private readonly CouponDuplicatePost $duplicatePost,
        private readonly CouponCodeGenerator $couponCodeGenerator,
    ) {
    }

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

		return CouponMapper::map(PostSnapshot::fromWpPost($wpPost));
	}

	/**
	 * @param array<int> $ids
	 */
    public function findMany(array $ids): CouponCollection
    {
        $couponIds = array_map(static fn (mixed $couponId): int => (int) $couponId, $ids);
        $couponIds = array_filter($couponIds, static fn (int $couponId): bool => $couponId > 0);
        $couponIds = array_values(array_unique($couponIds));

        if ($couponIds === []) {
            return CouponCollection::empty();
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

    public function findByCriteria(CouponCriteria $criteria): CouponListResponse
    {
        $query = WpCouponQueryBuilder::fromCriteria($criteria)->toWpQuery();
        $coupons = array_map(
            static fn (\WP_Post $post) => CouponListItemMapper::map(PostSnapshot::fromWpPost($post)),
            $query->posts,
        );

        $pagination = Pagination::of(
            totalItems: (int) $query->found_posts,
            currentPage: $criteria->page,
            perPage: $criteria->perPage,
        );

        return CouponListResponse::from(...$coupons)->withPagination($pagination);
    }

    public function getCountsByStatus(): StatusCountsInterface
    {
        return $this->mapWpCountsToStatusCounts(wp_count_posts(CouponPost::POST_TYPE));
    }

    public function saveStatus(CouponId $couponId, \Contexis\Events\Shared\Domain\ValueObjects\Status $status): void
    {
        wp_update_post([
            'ID' => $couponId->toInt(),
            'post_status' => $status->value,
        ]);
    }

    public function delete(CouponId $couponId): bool
    {
        $post = get_post($couponId->toInt());
        if (!$post instanceof \WP_Post || $post->post_type !== CouponPost::POST_TYPE) {
            return false;
        }

        return wp_delete_post($couponId->toInt(), true) !== false;
    }

    public function duplicateMany(CouponId $couponId, int $count): array
    {
        $sourceCoupon = $this->get($couponId);

        $created = [];
        for ($index = 1; $index <= $count; $index++) {
            $newPostId = $this->duplicatePost->duplicate($couponId->toInt());
            if ($newPostId === null) {
                throw new \RuntimeException('Failed to duplicate coupon.');
            }

            $code = $this->generateUniqueCode();
            update_post_meta($newPostId, CouponMeta::CODE, $code);
            wp_update_post([
                'ID' => $newPostId,
                'post_title' => sprintf('%s (%d)', $sourceCoupon->name, $index),
            ]);

            $created[] = CouponId::from($newPostId);
        }

        return $created;
    }

    private function generateUniqueCode(): string
    {
        do {
            $candidate = $this->couponCodeGenerator->generate();
        } while ($this->findByCode($candidate) !== null);

        return $candidate;
    }
}
