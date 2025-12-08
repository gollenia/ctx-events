<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

use Contexis\Events\Shared\Application\ValueObjects\Taxonomy;
use Contexis\Events\Shared\Application\ValueObjects\TaxonomyCollection;

final class TaxonomyLoader
{
    public function termsForPost(int $postId, string $taxonomy): ?TaxonomyCollection
    {
		var_dump($postId);
		var_dump($taxonomy);
        $terms = wp_get_object_terms($postId, $taxonomy, ['fields' => 'all']);

		
        if (is_wp_error($terms) || empty($terms)) {		
            return null;
        }

		
        $terms = array_map(
            static fn(\WP_Term $term) => new Taxonomy(
                id: (int)$term->term_id,
                slug: $term->slug,
                name: $term->name,
                taxonomy: $taxonomy,
				description: $term->description
            ),
            $terms
        );
		

        return TaxonomyCollection::fromArray($terms);
    }
}
