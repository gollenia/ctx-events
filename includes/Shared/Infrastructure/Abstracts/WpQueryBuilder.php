<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Abstracts;

use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;

abstract class WpQueryBuilder
{
    protected array $args = [
        'meta_query'     => ['relation' => 'AND'],
        'tax_query'      => ['relation' => 'AND'],
        'fields'         => 'all',
    ];

    public function addArg(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->args[$key] = $value;
        return $clone;
    }

    public function addArgs(array $args): self
    {
        $clone = clone $this;
        foreach ($args as $key => $value) {
            $clone->args[$key] = $value;
        }
        return $clone;
    }

    public function withTaxonomy(string $taxonomy, array $terms = [], string $field = 'term_id'): self
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

    public function withStatus(StatusList $status): self
    {
        $clone = clone $this;
        $clone->args['post_status'] = $status->toArray();
        return $clone;
    }

    protected function withPostType(string $postType): self
    {
        $clone = clone $this;
        $clone->args['post_type'] = $postType;
        return $clone;
    }

    protected function withPagination(int $page, int $perPage): self
    {
        $clone = clone $this;
        $clone->args['posts_per_page'] = $perPage;
        $clone->args['paged'] = max(1, $page);
        return $clone;
    }

    public function withCache(): self
    {
        $clone = clone $this;
        $clone->args['update_post_meta_cache'] = true;
        $clone->args['update_post_term_cache'] = true;
        return $clone;
    }

    public function withMetaEquals(string $key, string $value, string $type = 'CHAR'): self
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

    public function withMetaCompare(string $key, string $value, string $compare = '=', string $type = 'CHAR'): self
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

    public function withMetaQuery(array $metaQuery): self
    {
        $clone = clone $this;
        $clone->args['meta_query'][] = $metaQuery;
        return $clone;
    }

    public function orderBy(OrderBy $orderBy): self
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

    public function withSearch(string $search): self
    {
        $clone = clone $this;
        $clone->args['s'] = $search;
        return $clone;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function toWpQuery(): \WP_Query
    {
        return new \WP_Query($this->getArgs());
    }
}
