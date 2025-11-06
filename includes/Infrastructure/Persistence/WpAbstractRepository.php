<?php

namespace Contexis\Events\Infrastructure\Persistence;

use Contexis\Events\Core\Contracts\QueryOptions;
use Contexis\Events\Domain\ValueObjects\Id\AbstractId;
use Contexis\Events\Infrastructure\PostTypes\PostSnapshot;
use WP_Query;
use WP_Post;

abstract class WpAbstractRepository
{
    public const POST_TYPE_CLASS = "";
    protected ?QueryOptions $options = null;
    protected ?WP_Query $last_wp_query = null;

    private function reset(): void
    {
        $this->options = null;
        $this->last_wp_query = null;
    }

    public function query(QueryOptions $options): static
    {
        $this->reset();
        $this->options = $options;
        return $this;
    }

    private function getWpQuery(array $additionalArgs = []): WP_Query
    {
        if (empty($additionalArgs) && $this->last_wp_query !== null) {
            return $this->last_wp_query;
        }
        $args = $this->options ? $this->options->toArray() : [];
        $args = array_merge($args, $additionalArgs);
        $wp_query = new WP_Query($args);
        $this->last_wp_query = $wp_query;
        $this->options = null;
        return $wp_query;
    }

    public function postToArray(WP_Post $event, bool $include_meta = false): array
    {
        $data = $event->to_array();

        if ($include_meta) {
            $data['meta'] = get_post_meta($event->ID);
        }

        return $data;
    }

    public function getSnapshot(?AbstractId $id): ?PostSnapshot
    {
        if (!$id) {
            return null;
        }
        $post = get_post($id->toInt());
        if (!$post) {
            return null;
        }
        return new PostSnapshot($post);
    }

    public function get(): mixed
    {
        $wp_query = $this->getWpQuery();

        if (!$wp_query->have_posts()) {
            return [];
        }

        return $wp_query->get_posts();
    }

    public function first(): mixed
    {
        $wp_query = $this->getWpQuery(['posts_per_page' => 1]);

        if (!$wp_query->have_posts()) {
            return null;
        }

        return new PostSnapshot($wp_query->posts[0]);
    }

    public function count(): int
    {
        if ($this->last_wp_query !== null) {
            return (int) $this->last_wp_query->found_posts;
        }

        $wp_query = $this->getWpQuery(['fields' => 'ids', 'posts_per_page' => 1, 'no_found_rows' => false]);
        return (int) $wp_query->found_posts;
    }
}
