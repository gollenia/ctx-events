<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Abstracts;

use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;

abstract class WpQueryBuilder
{
	/** @var array<string, mixed> */
    protected array $args = [
        'meta_query'     => ['relation' => 'AND'],
        'tax_query'      => ['relation' => 'AND'],
        'fields'         => 'all',
    ];

    public function addArg(string $key, mixed $value): static
    {
        $clone = clone $this;
        $clone->args[$key] = $value;
        return $clone;
    }

	/**
	 * @param array<string, mixed> $args
	 */
    public function addArgs(array $args): static
    {
        $clone = clone $this;
        foreach ($args as $key => $value) {
            $clone->args[$key] = $value;
        }
        return $clone;
    }
    
	/**
	 * @param string $taxonomy
	 * @param array<int, string|int> $terms
	 * @param literal-string $field
	 */
    public function withTaxonomy(string $taxonomy, array $terms = [], string $field = 'term_id'): static
    {
        if (empty($terms)) {
            return $this;
        }
        $clone = clone $this;
        $clone->args['tax_query'][] = [
            'taxonomy' => $taxonomy,
            'field'    => $field,
            'terms'    => $terms,
        ];
        return $clone;
    }

    public function withStatus(StatusList $status): static
    {
        $clone = clone $this;
        $clone->args['post_status'] = $status->toArray();
        return $clone;
    }

	/**
	 * @param string|array<string> $postType
	 */
    protected function withPostType(string|array $postType): static
    {
        $clone = clone $this;
        $clone->args['post_type'] = $postType;
        return $clone;
    }

    protected function withPagination(int $page, int $perPage): static
    {
        $clone = clone $this;
        $clone->args['posts_per_page'] = $perPage;
        $clone->args['paged'] = max(1, $page);
        return $clone;
    }

    public function withCache(): static
    {
        $clone = clone $this;
        $clone->args['update_post_meta_cache'] = true;
        $clone->args['update_post_term_cache'] = true;
        return $clone;
    }

    public function withMetaEquals(string $key, string $value, string $type = 'CHAR'): static
    {
        $clone = clone $this;
        $clone->args['meta_query'][] = [
            'key'   => $key,
            'value' => $value,
            'compare' => '=',
            'type' => $type,
        ];
        return $clone;
    }

    public function withMetaCompare(string $key, string $value, string $compare = '=', string $type = 'CHAR'): static
    {
        $clone = clone $this;
        $clone->args['meta_query'][] = [
            'key'   => $key,
            'value' => $value,
            'compare' => $compare,
            'type' => $type,
        ];
        return $clone;
    }

	/**
	 * @param array<mixed> $metaQuery
	 */
    public function withMetaQuery(array $metaQuery): static
    {
        $clone = clone $this;
        $clone->args['meta_query'][] = $metaQuery;
        return $clone;
    }

    public function orderBy(OrderBy $orderBy): static
    {
        $clone = clone $this;
        if ($orderBy->isMeta) {
            $clone->args['meta_key'] = $orderBy->field;
            $clone->args['orderby'] = 'meta_value';
        } else {
            $clone->args['orderby'] = $orderBy->field;
        }

        $clone->args['order'] = $orderBy->order->value;
        return $clone;
    }

    public function withSearch(string $search): static
    {
        $clone = clone $this;
        $clone->args['s'] = $search;
        return $clone;
    }

	/**
	 * @return array<string, mixed> $args
	 */
    public function getArgs(): array
    {
        return $this->args;
    }

    public function toWpQuery(): \WP_Query
    {
        return new \WP_Query($this->getArgs());
    }
}
